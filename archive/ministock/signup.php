<?php
session_start();
require_once 'config/condb.php';

$error = '';
$name  = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // validate ฝั่ง server (หน้าเว็บข้ามได้)
    if ($name === '' || $email === '' || $password === '') {
        $error = 'กรุณากรอกให้ครบ';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'รูปแบบอีเมลไม่ถูกต้อง';
    } elseif (strlen($password) < 8) {
        $error = 'รหัสผ่านต้องยาวอย่างน้อย 8 ตัว';
    } else {
        try {
            $stmt = $condb->prepare("INSERT INTO customers (name, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);

            // สมัครเสร็จ → ล็อกอินให้เลย
            session_regenerate_id(true);
            $_SESSION['customer_id']   = (int)$condb->lastInsertId();
            $_SESSION['customer_name'] = $name;
            header('Location: ' . (($_GET['next'] ?? '') === 'cart' ? 'cart.php' : 'index.php'));
            exit;

        } catch (PDOException $e) {
            // 23000 = ชน UNIQUE → อีเมลนี้มีคนใช้แล้ว (ฐานข้อมูลเป็นด่านสุดท้ายกันซ้ำ)
            $error = ($e->getCode() == '23000') ? 'อีเมลนี้สมัครไว้แล้ว' : 'ระบบขัดข้อง ลองใหม่อีกครั้ง';
        }
    }
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>สมัครสมาชิก — Mini Shopping</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'nav.php'; ?>
<div class="container pb-5" style="max-width: 420px;">
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <h4 class="mb-4 text-center">สมัครสมาชิก</h4>
      <?php if ($error): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">ชื่อ</label>
          <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="form-control" required autocomplete="name">
        </div>
        <div class="mb-3">
          <label class="form-label">อีเมล</label>
          <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" class="form-control" required autocomplete="email">
        </div>
        <div class="mb-4">
          <label class="form-label">รหัสผ่าน (อย่างน้อย 8 ตัว)</label>
          <input type="password" name="password" minlength="8" class="form-control" required autocomplete="new-password">
        </div>
        <button class="btn btn-primary w-100">สมัครสมาชิก</button>
      </form>
      <p class="text-center small mt-3 mb-0">มีบัญชีแล้ว? <a href="signin.php">เข้าสู่ระบบ</a></p>
    </div>
  </div>
</div>
</body>
</html>
