<?php

/**
 * register.php — Member Registration (SHA-512 Standard)
 * Location: /views/register.php
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
  $name     = trim($_POST['name'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($name === '' || $email === '' || $password === '') {
    $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'รูปแบบอีเมลไม่ถูกต้อง';
  } else {
    if ($pdo) {
      try {
        // เช็คว่ามี email ซ้ำหรือไม่
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
          $error = 'อีเมลนี้ถูกใช้งานแล้ว';
        } else {
          // แปลง Password เป็น SHA-512 Hex (128 characters)
          $hashed_password = hash('sha512', $password);

          // ใช้อีเมลเป็น username ชั่วคราว
          $username = $email;
          $stmt = $pdo->prepare('INSERT INTO users (username, password, fullname, email, role) VALUES (?, ?, ?, ?, ?)');
          $stmt->execute([$username, $hashed_password, $name, $email, 'member']);

          // Login อัตโนมัติหลังสมัครเสร็จ
          $_SESSION['member_id']   = (int)$pdo->lastInsertId();
          $_SESSION['member_name'] = $name;
          $_SESSION['role']        = 'member';

          header('Location: /index.php');
          exit;
        }
      } catch (PDOException $e) {
        $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง';
      }
    } else {
      // Fallback กรณี DB ยังไม่เชื่อมต่อ
      $_SESSION['member_id']   = 999;
      $_SESSION['member_name'] = $name;
      $_SESSION['role']        = 'member';
      header('Location: /index.php');
      exit;
    }
  }
}

$page_title = "Register — DiffLovelyPet";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="section" style="padding: 60px 0;">
  <div class="container" style="max-width: 440px;">

    <div style="background: var(--bg-surface); padding: 32px; border-radius: var(--radius-card); box-shadow: var(--shadow-card);">

      <div style="text-align: center; margin-bottom: 24px;">
        <span class="status-badge is-active" style="margin-bottom: 8px; display: inline-block;">JOIN COMMUNITY</span>
        <h2 style="margin: 0 0 8px; font-weight: 800; font-size: 1.8rem;">สมัครสมาชิก</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">สร้างบัญชีเพื่อเริ่มต้นสร้าง Digital Pet Passport</p>
      </div>

      <?php if ($error): ?>
        <div style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 12px 16px; border-radius: var(--radius-input); margin-bottom: 20px; font-size: 0.9rem; font-weight: 500;">
          ⚠️ <?= h($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="/views/register.php" style="display: flex; flex-direction: column; gap: 18px;">
        <div>
          <label style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem;">Full Name / Display Name</label>
          <input type="text" name="name" value="<?= h($_POST['name'] ?? '') ?>" required placeholder="เช่น พี่ดิฟ LovelyPet" style="width: 100%; padding: 10px 14px; border-radius: var(--radius-input); border: 1px solid #ccc; font-size: 0.95rem; box-sizing: border-box;">
        </div>

        <div>
          <label style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem;">Email Address</label>
          <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required placeholder="name@example.com" style="width: 100%; padding: 10px 14px; border-radius: var(--radius-input); border: 1px solid #ccc; font-size: 0.95rem; box-sizing: border-box;">
        </div>

        <div>
          <label style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem;">Password</label>
          <input type="password" name="password" required placeholder="อย่างน้อย 6 ตัวอักษร" style="width: 100%; padding: 10px 14px; border-radius: var(--radius-input); border: 1px solid #ccc; font-size: 0.95rem; box-sizing: border-box;">
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 6px; width: 100%; padding: 12px; font-weight: 600;">
          สมัครสมาชิก (Create Account)
        </button>
      </form>

      <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid #eee; text-align: center; font-size: 0.9rem; color: var(--text-muted);">
        มีบัญชีผู้ใช้งานอยู่แล้ว? <a href="/views/login.php" style="color: var(--brand-color, #4f46e5); font-weight: 600; text-decoration: none;">เข้าสู่ระบบที่นี่</a>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>