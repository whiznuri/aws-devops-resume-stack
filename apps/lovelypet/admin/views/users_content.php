<!-- /admin/views/users_content.php -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h2 style="margin: 0; font-size: 1.6rem;">👥 จัดการสมาชิกและกำหนดสิทธิ์ (User Management)</h2>
    <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 0.9rem;">
      ค้นหา เปลี่ยนแปลงสิทธิ์การใช้งาน หรือลบบัญชีผู้ใช้ในระบบ
    </p>
  </div>

  <form method="get" style="display: flex; gap: 8px;">
    <input type="text" name="search" value="<?= h($search ?? '') ?>" placeholder="ค้นหาชื่อ หรืออีเมล..." style="padding: 8px 14px; border-radius: var(--radius-input); border: 1px solid #ccc; font-size: 0.9rem; width: 220px;">
    <button type="submit" class="btn btn-primary btn-sm">ค้นหา</button>
    <?php if (!empty($search)): ?>
      <a href="/admin/users.php" class="btn btn-secondary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center;">ล้างค่า</a>
    <?php endif; ?>
  </form>
</div>

<?php if ($message): ?>
  <div style="background: var(--pastel-mint); color: var(--pastel-mint-text); padding: 12px 20px; border-radius: var(--radius-input); margin-bottom: 20px; font-weight: 500;">
    ✅ <?= h($message) ?>
  </div>
<?php endif; ?>
<?php if ($error): ?>
  <div style="background: var(--pastel-pink); color: var(--pastel-pink-text); padding: 12px 20px; border-radius: var(--radius-input); margin-bottom: 20px; font-weight: 500;">
    ⚠️ <?= h($error) ?>
  </div>
<?php endif; ?>

<div style="background: var(--bg-surface); padding: 24px; border-radius: var(--radius-card); box-shadow: var(--shadow-card);">
  <div style="overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
      <thead>
        <tr style="border-bottom: 2px solid var(--bg-page); color: var(--text-muted);">
          <th style="padding: 12px;">ID</th>
          <th style="padding: 12px;">ชื่อผู้ใช้ / ชื่อเต็ม</th>
          <th style="padding: 12px;">อีเมล</th>
          <th style="padding: 12px;">สิทธิ์ปัจจุบัน</th>
          <th style="padding: 12px; text-align: right;">การดำเนินการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($users)): ?>
          <?php foreach ($users as $u): ?>
            <tr style="border-bottom: 1px solid var(--bg-page);">
              <td style="padding: 12px; font-weight: 600;">#<?= h((string)$u['id']) ?></td>
              <td style="padding: 12px;">
                <strong><?= h($u['username']) ?></strong>
                <div style="font-size: 0.8rem; color: var(--text-muted);"><?= h($u['fullname'] ?? '-') ?></div>
              </td>
              <td style="padding: 12px; color: var(--text-muted);"><?= h($u['email']) ?></td>
              <td style="padding: 12px;">
                <?php 
                  $r = $u['role'] ?? 'member';
                  $badgeClass = ($r === 'admin') ? 'is-blocked' : (($r === 'staff') ? 'is-pending' : 'is-active');
                ?>
                <span class="status-badge <?= $badgeClass ?>"><?= strtoupper(h($r)) ?></span>
              </td>
              <td style="padding: 12px; text-align: right;">
                <?php if ((int)$u['id'] === (int)($_SESSION['member_id'] ?? 0)): ?>
                  <span style="font-size: 0.8rem; color: var(--text-muted); background: #eee; padding: 4px 10px; border-radius: 12px; font-style: italic;">
                    บัญชีของคุณ
                  </span>
                <?php else: ?>
                  <div style="display: inline-flex; gap: 8px; align-items: center;">
                    <form method="post" style="display: inline-flex; gap: 6px; align-items: center;">
                      <input type="hidden" name="action" value="update_role">
                      <input type="hidden" name="user_id" value="<?= h((string)$u['id']) ?>">
                      
                      <select name="new_role" style="padding: 4px 8px; border-radius: var(--radius-input); border: 1px solid #ccc; font-size: 0.85rem;">
                        <option value="member" <?= $r === 'member' ? 'selected' : '' ?>>Member</option>
                        <option value="staff" <?= $r === 'staff' ? 'selected' : '' ?>>Staff</option>
                        <option value="admin" <?= $r === 'admin' ? 'selected' : '' ?>>Admin</option>
                      </select>
                      
                      <button type="submit" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 0.8rem;">อัปเดต</button>
                    </form>

                    <form method="post" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้นี้?');" style="display: inline;">
                      <input type="hidden" name="action" value="delete_user">
                      <input type="hidden" name="user_id" value="<?= h((string)$u['id']) ?>">
                      <button type="submit" class="btn btn-sm" style="background: #f8d7da; color: #721c24; border: none; padding: 4px 10px; border-radius: var(--radius-input); font-size: 0.8rem; cursor: pointer;">ลบ</button>
                    </form>
                  </div>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" style="padding: 20px; text-align: center; color: var(--text-muted);">ไม่พบข้อมูลสมาชิกในระบบ</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>