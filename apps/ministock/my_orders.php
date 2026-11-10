<?php
session_start();
require_once 'config/condb.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: signin.php');
    exit;
}

// ออเดอร์ของลูกค้าคนนี้ + รายการในแต่ละออเดอร์ (JOIN 3 ตาราง)
$stmt = $condb->prepare(
    "SELECT o.id AS order_id, o.total, o.created_at,
            p.name AS product, oi.qty, oi.price
     FROM orders o
     JOIN order_items oi ON oi.order_id = o.id
     JOIN products p     ON p.id = oi.product_id
     WHERE o.customer_id = ?
     ORDER BY o.id DESC, oi.id"
);
$stmt->execute([$_SESSION['customer_id']]);

// จัดกลุ่มรายการตามออเดอร์: [order_id => [ข้อมูลหัว + items]]
$orders = [];
foreach ($stmt->fetchAll() as $row) {
    $id = $row['order_id'];
    $orders[$id] ??= ['total' => $row['total'], 'created_at' => $row['created_at'], 'items' => []];
    $orders[$id]['items'][] = $row;
}
$new = (int)($_GET['new'] ?? 0);
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ออเดอร์ของฉัน — Mini Shopping</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'nav.php'; ?>

<div class="container pb-5" style="max-width: 820px;">
  <h4 class="mb-3">🧾 ออเดอร์ของฉัน</h4>

  <?php if ($new): ?>
    <div class="alert alert-success">สั่งซื้อสำเร็จ! ออเดอร์ #<?= $new ?></div>
  <?php endif; ?>

  <?php if (!$orders): ?>
    <p class="text-muted">ยังไม่มีออเดอร์</p>
  <?php endif; ?>

  <?php foreach ($orders as $id => $o): ?>
  <div class="card border-0 shadow-sm mb-3 <?= $id === $new ? 'border border-success' : '' ?>">
    <div class="card-body">
      <div class="d-flex justify-content-between mb-2">
        <strong>ออเดอร์ #<?= (int)$id ?></strong>
        <span class="small text-muted"><?= htmlspecialchars($o['created_at']) ?></span>
      </div>
      <table class="table table-sm mb-2">
        <?php foreach ($o['items'] as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['product']) ?></td>
            <td class="text-end">× <?= (int)$it['qty'] ?></td>
            <td class="text-end" style="width:120px"><?= number_format($it['price'] * $it['qty'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
      <div class="text-end fw-bold">รวม <?= number_format($o['total'], 2) ?> ฿</div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
</body>
</html>
