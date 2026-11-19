<?php

/**
 * login.php — Member Login (Official Standard)
 * Location: /views/login.php
 */

declare(strict_types=1);
// [REMARK: แก้ไขบรรทัดที่ 7] เช็กว่า Session ถูกเปิดไว้แล้วหรือยัง ก่อนเรียกใช้ เพื่อป้องกัน Session Active Notice
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../includes/db.php';

// Helper function กรณีไม่ได้ดึงผ่าน central controller
if (!function_exists('h')) {
  function h(?string $value): string
  {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
  }
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '' || $password === '') {
    $error = 'กรุณากรอกอีเมลและรหัสผ่าน';
  } else {
    if ($pdo) {
      try {
        $stmt = $pdo->prepare('SELECT id, fullname, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // แปลง Password เป็น SHA-512 Hex (128 characters)
        $hashed_input = hash('sha512', $password);

        if ($user && $hashed_input === $user['password']) {
          $_SESSION['member_id']   = (int)$user['id'];
          $_SESSION['member_name'] = $user['fullname'];
          $_SESSION['role']        = $user['role'];

          header('Location: /index.php');
          exit;
        } else {
          $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
        }
      } catch (PDOException $e) {
        $error = 'เกิดข้อผิดพลาดในการเชื่อมต่อระบบ กรุณาลองใหม่อีกครั้ง';
      }
    } else {
      $error = 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้';
    }
  }
}

$page_title = "Login — DiffLovelyPet";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="section" style="padding: 60px 0;">
  <div class="container" style="max-width: 440px;">

    <div style="background: var(--bg-surface); padding: 32px; border-radius: var(--radius-card); box-shadow: var(--shadow-card);">

      <div style="text-align: center; margin-bottom: 24px;">
        <span class="status-badge is-active" style="margin-bottom: 8px; display: inline-block;">WELCOME BACK</span>
        <h2 style="margin: 0 0 8px; font-weight: 800; font-size: 1.8rem;">เข้าสู่ระบบ</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">เข้าสู่ระบบเพื่อจัดการข้อมูลสัตว์เลี้ยงของคุณ</p>
      </div>

      <?php if ($error): ?>
        <div style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 12px 16px; border-radius: var(--radius-input); margin-bottom: 20px; font-size: 0.9rem; font-weight: 500;">
          ⚠️ <?= h($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="/views/login.php" style="display: flex; flex-direction: column; gap: 18px;">
        <div>
          <label style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem;">Email Address</label>
          <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required placeholder="name@example.com" style="width: 100%; padding: 10px 14px; border-radius: var(--radius-input); border: 1px solid #ccc; font-size: 0.95rem; box-sizing: border-box;">
        </div>

        <div>
          <label style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem;">Password</label>
          <input type="password" name="password" required placeholder="••••••••" style="width: 100%; padding: 10px 14px; border-radius: var(--radius-input); border: 1px solid #ccc; font-size: 0.95rem; box-sizing: border-box;">
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 6px; width: 100%; padding: 12px; font-weight: 600;">
          เข้าสู่ระบบ (Login)
        </button>
      </form>

      <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid #eee; text-align: center; font-size: 0.9rem; color: var(--text-muted);">
        ยังไม่มีบัญชีผู้ใช้งาน? <a href="/views/register.php" style="color: var(--brand-color, #4f46e5); font-weight: 600; text-decoration: none;">สมัครสมาชิกที่นี่</a>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>