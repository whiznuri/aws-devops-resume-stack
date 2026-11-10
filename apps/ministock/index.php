<?php
session_start();
require_once 'config/condb.php';

// ดึงเฉพาะสินค้าที่เปิดขายอยู่
$products = $condb->query("SELECT * FROM products WHERE is_active = 1 ORDER BY id")->fetchAll();
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mini Shopping</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'nav.php'; ?>

<div class="container pb-5" style="max-width: 820px;">

  <?php if (isset($_GET['added'])): ?>
    <div class="alert alert-success">ใส่ตะกร้าแล้ว <a href="cart.php">ดูตะกร้า →</a></div>
  <?php endif; ?>

  <div class="row g-3">
    <?php foreach ($products as $p): ?>
    <div class="col-sm-6 col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
          <p class="fs-5 fw-bold text-primary mb-1"><?= number_format($p['price']) ?> ฿</p>
          <p class="small mb-3">
            <?php if ($p['stock'] > 0): ?>
              <span class="text-success">มีสินค้า <?= (int)$p['stock'] ?> ชิ้น</span>
            <?php else: ?>
              <span class="text-muted">สินค้าหมด</span>
            <?php endif; ?>
          </p>

          <?php if ($p['stock'] > 0): ?>
          <form action="cart.php" method="post" class="d-flex gap-2 mt-auto">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <input type="number" name="qty" value="1" min="1" max="<?= (int)$p['stock'] ?>"
                   class="form-control form-control-sm" style="width:70px">
            <button class="btn btn-sm btn-primary flex-grow-1">ใส่ตะกร้า</button>
          </form>
          <?php else: ?>
            <button class="btn btn-sm btn-secondary mt-auto" disabled>สินค้าหมด</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>
