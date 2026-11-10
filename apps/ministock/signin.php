<?php
session_start();
require_once 'config/condb.php';

$error = '';
$email = '';
$next  = (($_GET['next'] ?? '') === 'cart') ? 'cart' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $condb->prepare("SELECT id, name, password_hash FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $c = $stmt->fetch();

    if ($c && password_verify($password, $c['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['customer_id']   = (int)$c['id'];
        $_SESSION['customer_name'] = $c['name'];
        header('Location: ' . ($next === 'cart' ? 'cart.php' : 'index.php'));
        exit;
    }
    $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>เข้าสู่ระบบ — Mini Shopping</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'nav.php'; ?>
<div class="container pb-5" style="max-width: 420px;">
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <h4 class="mb-4 text-center">เข้าสู่ระบบ</h4>
      <?php if ($next): ?><div class="alert alert-info py-2 small">เข้าสู่ระบบก่อนสั่งซื้อ ของในตะกร้ายังอยู่ครบ</div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">อีเมล</label>
          <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" class="form-control" required autocomplete="email">
        </div>
        <div class="mb-4">
          <label class="form-label">รหัสผ่าน</label>
          <input type="password" name="password" class="form-control" required autocomplete="current-password">
        </div>
        <button class="btn btn-primary w-100">เข้าสู่ระบบ</button>
      </form>
      <p class="text-center small mt-3 mb-0">ยังไม่มีบัญชี? <a href="signup.php<?= $next ? '?next=cart' : '' ?>">สมัครสมาชิก</a></p>
    </div>
  </div>
</div>
</body>
</html>
