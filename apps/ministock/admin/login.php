<?php
session_start();
require_once '../config/condb.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'กรุณากรอกให้ครบ';
    } else {
        $stmt = $condb->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        // password_verify: เอารหัสที่กรอกไปเทียบกับ hash ที่เก็บไว้
        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);   // เปลี่ยน session id ใหม่หลังล็อกอิน
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header('Location: index.php');
            exit;
        }
        // ไม่บอกว่าผิดที่ username หรือ password จะได้ไม่ช่วยคนเดา
        $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login — Mini Shopping</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 400px;">
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <h4 class="mb-4 text-center">🔐 หลังบ้าน</h4>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required autocomplete="username">
        </div>
        <div class="mb-4">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required autocomplete="current-password">
        </div>
        <button class="btn btn-dark w-100">เข้าสู่ระบบ</button>
      </form>
      <p class="text-center mt-3 mb-0"><a href="../index.php" class="small">← กลับหน้าร้าน</a></p>
    </div>
  </div>
</div>
</body>
</html>
