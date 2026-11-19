<?php
// แถบเมนูด้านบนของหน้าร้าน (include ในทุกหน้าลูกค้า)
$cart_count = count($_SESSION['cart'] ?? []);   // นับจำนวนรายการ ไม่ใช่จำนวนชิ้นรวม
?>
<nav class="navbar navbar-expand bg-white shadow-sm mb-4">
  <div class="container" style="max-width: 820px;">
    <a class="navbar-brand fw-bold" href="index.php">🛒 Mini Shopping</a>
    <div class="d-flex align-items-center gap-3 small">
      <a href="cart.php" class="text-decoration-none">
        ตะกร้า <span class="badge bg-primary"><?= (int)$cart_count ?></span>
      </a>
      <?php if (isset($_SESSION['customer_id'])): ?>
        <a href="my_orders.php" class="text-decoration-none">ออเดอร์ของฉัน</a>
        <span class="text-muted">สวัสดี <?= htmlspecialchars($_SESSION['customer_name']) ?></span>
        <a href="signout.php" class="text-decoration-none">ออกจากระบบ</a>
      <?php else: ?>
        <a href="signin.php" class="text-decoration-none">เข้าสู่ระบบ</a>
        <a href="signup.php" class="btn btn-sm btn-outline-primary">สมัครสมาชิก</a>
      <?php endif; ?>
      <a href="admin/" class="text-muted text-decoration-none">หลังบ้าน</a>
    </div>
  </div>
</nav>
