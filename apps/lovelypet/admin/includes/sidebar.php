<?php
/**
 * admin/includes/sidebar.php — Central Admin Navigation
 */
declare(strict_types=1);

$current_role = $_SESSION['role'] ?? 'member';
$current_page = $_GET['page'] ?? 'overview';
?>
<aside class="admin-sidebar">
  <div>
    <!-- Admin Brand -->
    <div style="margin-bottom: 28px; padding-left: 8px;">
      <a href="/index.php" class="brand" style="font-size: 1.2rem;">Diff<span>Lovely</span>Pet</a>
      <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; font-weight: 600;">
        ⚙️ <?= strtoupper(h($current_role)) ?> CONTROL
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="admin-nav">
      <ul style="display: flex; flex-direction: column; gap: 6px; list-style: none; padding: 0; margin: 0;">
        
        <li>
          <a href="/admin/dashboard.php?page=overview" class="admin-nav-item <?= $current_page === 'overview' ? 'active' : '' ?>">
            📊 Dashboard
          </a>
        </li>

        <li>
          <a href="/admin/dashboard.php?page=breeds" class="admin-nav-item <?= $current_page === 'breeds' ? 'active' : '' ?>">
            🐶 จัดการสายพันธุ์
          </a>
        </li>

        <li>
          <a href="/admin/dashboard.php?page=pets" class="admin-nav-item <?= $current_page === 'pets' ? 'active' : '' ?>">
            🐾 จัดการสัตว์เลี้ยง
          </a>
        </li>

        <li>
          <a href="/admin/dashboard.php?page=pet_types" class="admin-nav-item <?= $current_page === 'pet_types' ? 'active' : '' ?>">
            🏷️ จัดการประเภทสัตว์เลี้ยง
          </a>
        </li>

        <!-- ซ่อน/แสดง เมนู User Management ตามสิทธิ์ Admin เท่านั้น -->
        <?php if ($current_role === 'admin'): ?>
          <li style="margin-top: 16px; padding-top: 12px; border-top: 1px solid rgba(44, 62, 80, 0.08);">
            <small style="color: var(--text-muted); padding-left: 8px; font-size: 0.75rem; font-weight: 700; display: block; margin-bottom: 8px;">SYSTEM SECURITY</small>
          </li>
          <li>
            <a href="/admin/dashboard.php?page=users" class="admin-nav-item <?= $current_page === 'users' ? 'active' : '' ?>">
              👥 จัดการผู้ใช้ & สิทธิ์
            </a>
          </li>
        <?php endif; ?>

      </ul>
    </nav>
  </div>

  <!-- Footer ด้านล่าง Sidebar -->
  <div>
    <a href="/index.php" class="admin-nav-item" style="color: var(--text-muted);">
      ← กลับไปหน้าบ้าน
    </a>
  </div>
</aside>
