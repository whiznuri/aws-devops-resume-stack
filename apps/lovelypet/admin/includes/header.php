<?php
/**
 * admin/includes/header.php — Admin Layout Wrapper & Sidebar
 */
declare(strict_types=1);

if (!function_exists('h')) {
    function h(?string $value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$current_role = $_SESSION['role'] ?? 'member';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title ?? 'Admin Dashboard — DiffLovelyPet') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/public/assets/style.css">
<style>
  /* Layout โครงสร้างหลังบ้าน Flexbox (Sidebar + Content Area) */
  .admin-wrapper {
    display: flex;
    min-height: 100vh;
    background: var(--bg-page);
  }
  .admin-sidebar {
    width: 240px;
    flex-shrink: 0;
    background: var(--bg-surface);
    border-right: 1px solid rgba(44, 62, 80, 0.08);
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .admin-main {
    flex: 1;
    padding: 32px;
    overflow-x: hidden;
  }
  .admin-nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: var(--radius-input);
    color: var(--text-main);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: background 0.2s;
  }
  .admin-nav-item:hover, .admin-nav-item.active {
    background: var(--pastel-blue);
    color: var(--pastel-blue-text);
  }
</style>
</head>
<body>

<div class="admin-wrapper">
  <!-- Sidebar Component -->
  <?php 
  $sidebar_path = __DIR__ . '/sidebar.php';
  if (file_exists($sidebar_path)) {
      require_once $sidebar_path;
  }
  ?>

  <!-- Main Content Area เริ่มตรงนี้ -->
  <main class="admin-main">