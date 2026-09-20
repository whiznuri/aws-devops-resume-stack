<?php
/**
 * includes/navbar.php — Minimal Clean Header (No Search Box)
 * Location: /includes/navbar.php
 */
declare(strict_types=1);

$is_logged_in = $_SESSION['member_name'] ?? null;
$member_name  = $_SESSION['member_name'] ?? null;
$user_role        = $_SESSION['role'] ?? '';
$can_access_admin = in_array($user_role, ['admin', 'staff'], true);
$role_label = ($user_role === 'admin') ? '⚙️ Admin' : (($user_role === 'staff') ? '🛠️ Staff' : 'Panel');
$current_page = $_GET['page'] ?? 'home';
?>
<header class="site-header">
  <div class="container">
    <!-- 1. Brand Logo -->
    <a href="/index.php?page=home" class="brand">Diff<span>Lovely</span>Pet</a>

    <!-- Mobile Hamburger Toggle -->
    <button type="button" class="nav-toggle" id="navToggle" aria-label="เปิด/ปิดเมนู" aria-expanded="false" aria-controls="navCollapse">
      <span></span><span></span><span></span>
    </button>

    <!-- 2. Nav Collapse Wrapper (เมนู + ปุ่ม Auth) -->
    <div class="nav-collapse" id="navCollapse">
      <nav>
        <ul class="nav-links">
          <li><a href="/index.php?page=home" class="<?= $current_page === 'home' ? 'is-active' : '' ?>">Home</a></li>
          <li><a href="/index.php?page=encyclopedia" class="<?= $current_page === 'encyclopedia' ? 'is-active' : '' ?>">Encyclopedia</a></li>
          <li><a href="/index.php?page=catalog" class="<?= $current_page === 'catalog' ? 'is-active' : '' ?>">Pet Catalog</a></li>
          <li><a href="/index.php?page=leaderboard" class="<?= $current_page === 'leaderboard' ? 'is-active' : '' ?>">Leaderboard</a></li>
          <?php if (!$is_logged_in): ?>
            <li><a href="/index.php?page=register" class="<?= $current_page === 'register' ? 'is-active' : '' ?>">Register</a></li>
          <?php endif; ?>
          <li><a href="http://15.135.71.60:8080/" target="_blank" rel="noopener noreferrer">Contact</a></li>
        </ul>
      </nav>

      <div class="header-actions">
        <?php if ($is_logged_in): ?>
          <div class="auth-control">
            <?php if ($can_access_admin): ?>
              <a class="btn btn-secondary btn-sm" href="/admin/dashboard.php"><?= h($role_label) ?></a>
            <?php endif; ?>
            <span class="greeting" title="<?= h($member_name) ?>">
              <span class="heart">&#10084;</span> Hi, <?= h($member_name) ?>
            </span>
            <a class="btn btn-ghost btn-sm" href="/views/logout.php">Logout</a>
          </div>
        <?php else: ?>
          <div class="auth-control">
            <a class="btn btn-ghost btn-sm <?= $current_page === 'login' ? 'is-active' : '' ?>" href="/index.php?page=login">Login</a>
            <a class="btn btn-secondary btn-sm" href="/index.php?page=register">Register</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<script>
  (function () {
    var navToggle = document.getElementById('navToggle');
    var navCollapse = document.getElementById('navCollapse');
    if (!navToggle || !navCollapse) return;

    function closeMenu() {
      navCollapse.classList.remove('is-open');
      navToggle.classList.remove('is-active');
      navToggle.setAttribute('aria-expanded', 'false');
    }

    navToggle.addEventListener('click', function () {
      var isOpen = navCollapse.classList.toggle('is-open');
      navToggle.classList.toggle('is-active', isOpen);
      navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    navCollapse.querySelectorAll('a').forEach(function (el) {
      el.addEventListener('click', closeMenu);
    });
  })();
</script>