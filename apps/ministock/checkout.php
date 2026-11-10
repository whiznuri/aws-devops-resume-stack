<?php
// ===========================================================
// checkout.php — สั่งซื้อทั้งตะกร้าใน TRANSACTION เดียว
// สินค้าในตะกร้ามีกี่อย่างก็ได้ 1 ออเดอร์ + หลายแถวใน order_items
// ถ้าชิ้นไหนสต็อกไม่พอ → ยกเลิกทั้งออเดอร์ (ไม่ตัดสต็อกชิ้นอื่นด้วย)
// ===========================================================
session_start();
require_once 'config/condb.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

// ต้องล็อกอินก่อนสั่งซื้อ
if (!isset($_SESSION['customer_id'])) {
    header('Location: signin.php?next=cart');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    header('Location: cart.php');
    exit;
}

// เรียงตาม product_id → ทุกคนล็อกแถวตามลำดับเดียวกัน ลดโอกาส deadlock
ksort($cart);

try {
    $condb->beginTransaction();

    $total = 0;
    $lines = [];

    // 1) ล็อกและเช็กสต็อกสินค้าทุกชิ้นก่อน
    $lock = $condb->prepare("SELECT id, name, price, stock, is_active FROM products WHERE id = ? FOR UPDATE");
    foreach ($cart as $product_id => $qty) {
        $product_id = (int)$product_id;
        $qty        = (int)$qty;
        if ($qty <= 0) {
            throw new Exception('จำนวนสินค้าไม่ถูกต้อง');
        }

        $lock->execute([$product_id]);
        $p = $lock->fetch();

        if (!$p) {
            throw new Exception('มีสินค้าในตะกร้าที่ไม่มีอยู่แล้ว');
        }
        // เช็กซ้ำที่ server: หน้าตะกร้าอาจเปิดค้างไว้ก่อนแอดมินกดระงับ
        if (!$p['is_active']) {
            throw new Exception("{$p['name']} ระงับการขายแล้ว");
        }
        if ($p['stock'] < $qty) {
            throw new Exception("{$p['name']} สต็อกไม่พอ (เหลือ {$p['stock']})");
        }

        $lines[] = ['id' => $p['id'], 'qty' => $qty, 'price' => $p['price']];
        $total  += $p['price'] * $qty;
    }

    // 2) สร้างหัวออเดอร์ 1 แถว
    $stmt = $condb->prepare("INSERT INTO orders (customer_id, total) VALUES (?, ?)");
    $stmt->execute([$_SESSION['customer_id'], $total]);
    $order_id = $condb->lastInsertId();

    // 3) บันทึกรายการ + ตัดสต็อก ทีละชิ้น
    $addItem = $condb->prepare("INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
    $cutStock = $condb->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    foreach ($lines as $line) {
        $addItem->execute([$order_id, $line['id'], $line['qty'], $line['price']]);
        $cutStock->execute([$line['qty'], $line['id']]);
    }

    // ทุกชิ้นผ่านหมด → บันทึกจริงทั้งก้อน
    $condb->commit();

    $_SESSION['cart'] = [];   // ล้างตะกร้า
    header('Location: my_orders.php?new=' . $order_id);
    exit;

} catch (Exception $e) {
    if ($condb->inTransaction()) {
        $condb->rollBack();   // ยกเลิกทั้งออเดอร์ เหมือนไม่เคยเกิดขึ้น
    }
    header('Location: cart.php?err=' . urlencode($e->getMessage()));
    exit;
}
