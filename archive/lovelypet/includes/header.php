<?php
/**
 * includes/header.php — Shared Navigation & Layout Header (Parent Wrapper)
 * Location: /includes/header.php
 */
declare(strict_types=1);

// Helper function กัน XSS
if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title ?? 'LovelyPet — Pet Encyclopedia & Digital Pet Passport Community') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/public/assets/style.css">
<style>
  .auth-control .greeting {
    max-width: 130px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: inline-block;
    vertical-align: middle;
  }
</style>
</head>
<body>

<?php
// Safe Nesting: ดึง navbar.php มาแสดง ถ้าไฟล์หายไปก็ไม่ทำให้ระบบพัง (Safe Fallback)
$navbar_path = __DIR__ . '/navbar.php';
if (file_exists($navbar_path)) {
    require_once $navbar_path;
}
?>