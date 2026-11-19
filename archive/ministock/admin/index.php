<?php
require_once 'auth.php';            // ต้องล็อกอินก่อน
require_once '../config/condb.php';

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';

// ===========================================================
// จัดการฟอร์ม (เพิ่ม / แก้ / ลบ สินค้า)
// ===========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $name  = trim($_POST['name'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $stock = (int)($_POST['stock'] ?? 0);

            // validate ฝั่ง server
            if ($name === '' || $price <= 0 || $stock < 0) {
                throw new Exception('ข้อมูลสินค้าไม่ถูกต้อง');
            }
            $stmt = $condb->prepare("INSERT INTO products (name, price, stock) VALUES (?, ?, ?)");
            $stmt->execute([$name, $price, $stock]);
            $msg = 'เพิ่มสินค้าแล้ว';

        } elseif ($action === 'price') {
            // ราคา = ตั้งค่าใหม่ทับได้
            $id    = (int)($_POST['id'] ?? 0);
            $price = (float)($_POST['price'] ?? 0);

            if ($id <= 0 || $price <= 0) {
                throw new Exception('ราคาไม่ถูกต้อง');
            }
            $stmt = $condb->prepare("UPDATE products SET price = ? WHERE id = ?");
            $stmt->execute([$price, $id]);
            $msg = 'แก้ราคาแล้ว';

        } elseif ($action === 'restock') {
            // สต็อก = รับของเข้า บวกเพิ่มจากค่าล่าสุดในฐานข้อมูล (ไม่ทับค่า)
            // กัน lost update: ถ้ามีคนซื้อระหว่างแอดมินเปิดหน้าอยู่ ยอดขายนั้นไม่หาย
            $id     = (int)($_POST['id'] ?? 0);
            $qty_in = (int)($_POST['qty_in'] ?? 0);

            if ($id <= 0 || $qty_in <= 0) {
                throw new Exception('จำนวนรับเข้าต้องมากกว่า 0');
            }
            $stmt = $condb->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $stmt->execute([$qty_in, $id]);
            $msg = "รับของเข้า +$qty_in แล้ว";

        } elseif ($action === 'toggle') {
            // soft delete: ไม่ลบจริง แค่สลับเปิดขาย / ระงับการขาย
            // ประวัติออเดอร์กับรายงานยังอ้างถึงสินค้านี้ได้ครบ กดผิดก็เปิดกลับได้
            // ลบถาวรให้ทีม backend/DB ทำผ่านฐานข้อมูลโดยตรง ไม่เปิดให้ทำจากหน้าเว็บ
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('ข้อมูลไม่ถูกต้อง');
            }
            $stmt = $condb->prepare("UPDATE products SET is_active = 1 - is_active WHERE id = ?");
            $stmt->execute([$id]);
            $msg = 'เปลี่ยนสถานะการขายแล้ว';
        }

        header('Location: index.php?msg=' . urlencode($msg));
        exit;

    } catch (PDOException $e) {
        error_log('Admin DB error: ' . $e->getMessage());
        header('Location: index.php?err=' . urlencode('ฐานข้อมูลขัดข้อง'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?err=' . urlencode($e->getMessage()));
        exit;
    }
}

// ===========================================================
// ดึงข้อมูลมาแสดง
// ===========================================================

// สินค้าทั้งหมด
$products = $condb->query("SELECT * FROM products ORDER BY id")->fetchAll();

// ออเดอร์ล่าสุด: JOIN 3 ตาราง
$orders = $condb->query(
    "SELECT o.id AS order_id, c.name AS customer, o.created_at,
            p.name AS product, oi.qty, oi.price, (oi.qty * oi.price) AS subtotal
     FROM orders o
     JOIN customers c    ON c.id = o.customer_id
     JOIN order_items oi ON oi.order_id = o.id
     JOIN products p     ON p.id = oi.product_id
     ORDER BY o.id DESC, oi.id
     LIMIT 50"
)->fetchAll();

// รายงานยอดขาย: GROUP BY สินค้า
// LEFT JOIN เพื่อให้สินค้าที่ยังขายไม่ได้ก็ขึ้นด้วย (ยอด 0)
$report = $condb->query(
    "SELECT p.name,
            COALESCE(SUM(oi.qty), 0)            AS total_qty,
            COALESCE(SUM(oi.qty * oi.price), 0) AS total_sales
     FROM products p
     LEFT JOIN order_items oi ON oi.product_id = p.id
     GROUP BY p.id, p.name
     ORDER BY total_sales DESC"
)->fetchAll();

$grand_total = array_sum(array_column($report, 'total_sales'));
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin — Mini Shopping</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
  <div class="container">
    <span class="navbar-brand">🛠️ หลังบ้าน Mini Shopping</span>
    <div class="text-white small">
      สวัสดี <?= htmlspecialchars($_SESSION['admin_name']) ?> ·
      <a href="../index.php" class="link-light">หน้าร้าน</a> ·
      <a href="logout.php" class="link-light">ออกจากระบบ</a>
    </div>
  </div>
</nav>

<div class="container pb-5">

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="row g-4">

    <!-- ===== รายงานยอดขาย ===== -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">📊 ยอดขายตามสินค้า</h5>
          <p class="text-muted small mb-3">ยอดรวมทั้งหมด <strong><?= number_format($grand_total, 2) ?> ฿</strong></p>
          <table class="table table-sm">
            <thead><tr><th>สินค้า</th><th class="text-end">ขายได้ (ชิ้น)</th><th class="text-end">ยอดขาย</th></tr></thead>
            <tbody>
            <?php foreach ($report as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td class="text-end"><?= (int)$r['total_qty'] ?></td>
                <td class="text-end"><?= number_format($r['total_sales'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ===== จัดการสินค้า ===== -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">📦 จัดการสินค้า</h5>

          <table class="table table-sm align-middle">
            <thead><tr><th>สินค้า</th><th class="text-center">คงเหลือ</th><th>ราคา</th><th>รับของเข้า</th><th>สถานะ</th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
              <tr class="<?= $p['is_active'] ? '' : 'table-secondary text-muted' ?>">
                <td>
                  <?= htmlspecialchars($p['name']) ?>
                  <?php if (!$p['is_active']): ?><span class="badge bg-secondary ms-1">ระงับ</span><?php endif; ?>
                </td>
                <td class="text-center">
                  <span class="badge <?= $p['stock'] > 0 ? 'bg-success' : 'bg-secondary' ?>"><?= (int)$p['stock'] ?></span>
                </td>
                <td>
                  <form method="post" class="d-flex gap-1">
                    <input type="hidden" name="action" value="price">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <input type="number" step="0.01" min="0.01" name="price" value="<?= $p['price'] ?>" class="form-control form-control-sm" style="width:100px">
                    <button class="btn btn-sm btn-outline-primary">แก้</button>
                  </form>
                </td>
                <td>
                  <form method="post" class="d-flex gap-1">
                    <input type="hidden" name="action" value="restock">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <input type="number" min="1" name="qty_in" placeholder="+0" class="form-control form-control-sm" style="width:70px" required>
                    <button class="btn btn-sm btn-outline-success">รับเข้า</button>
                  </form>
                </td>
                <td>
                  <form method="post">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <?php if ($p['is_active']): ?>
                      <button class="btn btn-sm btn-outline-warning text-nowrap">ระงับขาย</button>
                    <?php else: ?>
                      <button class="btn btn-sm btn-outline-success text-nowrap">เปิดขาย</button>
                    <?php endif; ?>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>

          <h6 class="mt-4">เพิ่มสินค้าใหม่</h6>
          <form method="post" class="row g-2">
            <input type="hidden" name="action" value="add">
            <div class="col-md-5"><input type="text" name="name" class="form-control form-control-sm" placeholder="ชื่อสินค้า" required></div>
            <div class="col-md-3"><input type="number" step="0.01" min="0.01" name="price" class="form-control form-control-sm" placeholder="ราคา" required></div>
            <div class="col-md-2"><input type="number" min="0" name="stock" class="form-control form-control-sm" placeholder="สต็อก" required></div>
            <div class="col-md-2"><button class="btn btn-sm btn-success w-100">เพิ่ม</button></div>
          </form>
        </div>
      </div>
    </div>

    <!-- ===== ออเดอร์ล่าสุด ===== -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h5 class="card-title">🧾 ออเดอร์ล่าสุด</h5>
          <?php if (!$orders): ?>
            <p class="text-muted mb-0">ยังไม่มีออเดอร์</p>
          <?php else: ?>
          <table class="table table-sm">
            <thead><tr><th>#</th><th>ลูกค้า</th><th>สินค้า</th><th class="text-end">จำนวน</th><th class="text-end">ราคา/ชิ้น</th><th class="text-end">รวม</th><th>เวลา</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td><?= (int)$o['order_id'] ?></td>
                <td><?= htmlspecialchars($o['customer']) ?></td>
                <td><?= htmlspecialchars($o['product']) ?></td>
                <td class="text-end"><?= (int)$o['qty'] ?></td>
                <td class="text-end"><?= number_format($o['price'], 2) ?></td>
                <td class="text-end"><?= number_format($o['subtotal'], 2) ?></td>
                <td class="small text-muted"><?= htmlspecialchars($o['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>
</body>
</html>
