<?php
// ===========================================================
// cart.php — ตะกร้าสินค้า
// เก็บไว้ใน session:  $_SESSION['cart'] = [product_id => qty, ...]
// ยังไม่แตะฐานข้อมูลจนกว่าจะกดสั่งซื้อ (checkout.php)
// ===========================================================
session_start();
require_once 'config/condb.php';

$_SESSION['cart'] = $_SESSION['cart'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action'] ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);
    $qty        = (int)($_POST['qty'] ?? 0);

    if ($action === 'add' && $product_id > 0 && $qty > 0) {
        // มีอยู่แล้ว → บวกเพิ่ม / ยังไม่มี → ใส่ใหม่
        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $qty;
        header('Location: index.php?added=1');
        exit;
    }
    if ($action === 'update') {
        if (isset($_POST['remove'])) {
            // กดปุ่ม "เอาออก" ของแถวไหน → ลบแถวนั้น
            unset($_SESSION['cart'][(int)$_POST['remove']]);
        } else {
            // กด "อัปเดตตะกร้า" → แก้ทุกแถวพร้อมกัน  qty[product_id] = จำนวน
            foreach (($_POST['qty'] ?? []) as $pid => $q) {
                $pid = (int)$pid;
                $q   = (int)$q;
                if (!isset($_SESSION['cart'][$pid])) {
                    continue;                          // แก้ได้เฉพาะของที่อยู่ในตะกร้าอยู่แล้ว
                }
                if ($q > 0) {
                    $_SESSION['cart'][$pid] = $q;
                } else {
                    unset($_SESSION['cart'][$pid]);    // ใส่ 0 = เอาออก
                }
            }
        }
    }
    header('Location: cart.php');
    exit;
}

// ดึงข้อมูลสินค้าที่อยู่ในตะกร้ามาแสดง
$items = [];
$total = 0;
if ($_SESSION['cart']) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));   // ?,?,?
    $stmt = $condb->prepare("SELECT id, name, price, stock, is_active FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);

    foreach ($stmt->fetchAll() as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $p['qty']      = $qty;
        $p['subtotal'] = $p['price'] * $qty;
        // สั่งได้ต้องเปิดขายอยู่ และสต็อกพอ (สินค้าอาจถูกระงับหลังลูกค้าใส่ตะกร้าไปแล้ว)
        $p['enough']   = $p['is_active'] && $p['stock'] >= $qty;
        $total += $p['subtotal'];
        $items[] = $p;
    }
}
$all_enough = !in_array(false, array_column($items, 'enough'), true);
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ตะกร้า — Mini Shopping</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'nav.php'; ?>

<div class="container pb-5" style="max-width: 820px;">
  <h4 class="mb-3">🧺 ตะกร้าสินค้า</h4>

  <?php if (isset($_GET['err'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['err']) ?></div>
  <?php endif; ?>

  <?php if (!$items): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">
      ตะกร้ายังว่าง <a href="index.php">เลือกสินค้า →</a>
    </div></div>
  <?php else: ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <!-- ฟอร์มเดียวครอบทั้งตะกร้า แก้หลายแถวแล้วกดปุ่มเดียว -->
      <form method="post">
        <input type="hidden" name="action" value="update">

        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="small text-muted">แก้จำนวนได้หลายแถว แล้วกดอัปเดตครั้งเดียว (ใส่ 0 = เอาออก)</span>
          <!-- ปุ่มนี้ต้องอยู่ก่อนปุ่ม "เอาออก" ใน HTML: กด Enter ในช่องจำนวน = อัปเดต ไม่ใช่ลบ -->
          <button class="btn btn-sm btn-outline-primary">อัปเดตตะกร้า</button>
        </div>

        <table class="table align-middle">
          <thead><tr><th>สินค้า</th><th class="text-end">ราคา</th><th style="width:110px">จำนวน</th><th class="text-end">รวม</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td>
                <?= htmlspecialchars($it['name']) ?>
                <?php if (!$it['is_active']): ?>
                  <div class="small text-danger">สินค้านี้ระงับการขายแล้ว กรุณาเอาออก</div>
                <?php elseif (!$it['enough']): ?>
                  <div class="small text-danger">สต็อกเหลือแค่ <?= (int)$it['stock'] ?></div>
                <?php endif; ?>
              </td>
              <td class="text-end"><?= number_format($it['price'], 2) ?></td>
              <td>
                <input type="number" name="qty[<?= (int)$it['id'] ?>]" value="<?= (int)$it['qty'] ?>" min="0"
                       class="form-control form-control-sm" style="width:80px">
              </td>
              <td class="text-end"><?= number_format($it['subtotal'], 2) ?></td>
              <td class="text-end">
                <button name="remove" value="<?= (int)$it['id'] ?>" class="btn btn-sm btn-outline-danger">เอาออก</button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot><tr><th colspan="3" class="text-end">ยอดรวม</th><th class="text-end"><?= number_format($total, 2) ?> ฿</th><th></th></tr></tfoot>
        </table>
      </form>

      <div class="d-flex justify-content-between align-items-center">
        <a href="index.php">← เลือกสินค้าต่อ</a>
        <?php if (!isset($_SESSION['customer_id'])): ?>
          <a href="signin.php?next=cart" class="btn btn-primary">เข้าสู่ระบบเพื่อสั่งซื้อ</a>
        <?php elseif (!$all_enough): ?>
          <button class="btn btn-secondary" disabled>แก้ตะกร้าตามที่แจ้งเตือนก่อน</button>
        <?php else: ?>
          <form action="checkout.php" method="post">
            <button class="btn btn-success">ยืนยันสั่งซื้อ</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
