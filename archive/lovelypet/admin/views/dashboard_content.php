<!-- /admin/views/dashboard_content.php -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
  <div>
    <h2 style="margin: 0; font-size: 1.6rem;">📊 Control Center Overview</h2>
    <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 0.9rem;">
      ยินดีต้อนรับคุณ <strong><?= h($_SESSION['member_name'] ?? 'Team') ?></strong> 
      [<span style="text-transform: uppercase;"><?= h($_SESSION['role'] ?? 'member') ?></span>]
    </p>
  </div>
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

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 32px;">
  <div style="background: var(--pastel-blue); color: var(--pastel-blue-text); padding: 20px; border-radius: var(--radius-card);">
    <small style="font-weight: 600;">ผู้ใช้งานทั้งหมด</small>
    <h3 style="font-size: 2rem; margin: 8px 0 0; color: currentColor;"><?= $total_users ?></h3>
  </div>

  <div style="background: var(--pastel-mint); color: var(--pastel-mint-text); padding: 20px; border-radius: var(--radius-card);">
    <small style="font-weight: 600;">สัตว์เลี้ยงในระบบ</small>
    <h3 style="font-size: 2rem; margin: 8px 0 0; color: currentColor;"><?= $total_pets ?></h3>
  </div>

  <div style="background: var(--pastel-pink); color: var(--pastel-pink-text); padding: 20px; border-radius: var(--radius-card);">
    <small style="font-weight: 600;">สายพันธุ์ทั้งหมด</small>
    <h3 style="font-size: 2rem; margin: 8px 0 0; color: currentColor;"><?= $total_breeds ?></h3>
  </div>

  <div style="background: var(--pastel-blue); color: var(--pastel-blue-text); padding: 20px; border-radius: var(--radius-card);">
    <small style="font-weight: 600;">ประเภทสัตว์เลี้ยง</small>
    <h3 style="font-size: 2rem; margin: 8px 0 0; color: currentColor;"><?= $total_pet_types ?></h3>
  </div>
</div>

<?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
  <div style="background: var(--bg-surface); padding: 24px; border-radius: var(--radius-card); box-shadow: var(--shadow-card); margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
      <h3 style="margin: 0 0 6px 0;">👥 จัดการสมาชิกและกำหนดสิทธิ์ (User Management)</h3>
      <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">ค้นหา ตรวจสอบ และจัดการสิทธิ์ผู้ใช้งานระบบแบบเต็มรูปแบบ</p>
    </div>
    <a href="/admin/users.php" class="btn btn-primary btn-sm" style="text-decoration: none;">ไปที่หน้าจัดการผู้ใช้ ➔</a>
  </div>
<?php endif; ?>

<div style="background: var(--bg-surface); padding: 24px; border-radius: var(--radius-card); box-shadow: var(--shadow-card);">
  <h3 style="margin-bottom: 12px;">📋 การดำเนินการด่วน (Quick Operations)</h3>
  <p style="color: var(--text-muted); margin-bottom: 16px; font-size: 0.9rem;">จัดการข้อมูลสายพันธุ์ ประเภท และสัตว์เลี้ยงในฐานข้อมูล:</p>

  <div style="display: flex; gap: 12px; flex-wrap: wrap;">
    <!-- เดิมทั้ง 2 ปุ่มนี้เป็นแค่ alert('เตรียมพบกับ...') ไม่ได้ทำอะไรจริง —
         ตอนนี้ลิงก์ไปหน้าที่มีฟอร์มเพิ่ม/แก้ไขจริงๆ ให้แล้ว -->
    <a href="/admin/dashboard.php?page=breeds" class="btn btn-primary btn-sm" style="text-decoration: none;">+ เพิ่มสายพันธุ์ใหม่</a>
    <a href="/admin/dashboard.php?page=pet_types" class="btn btn-secondary btn-sm" style="text-decoration: none;">+ เพิ่มประเภทสัตว์เลี้ยง</a>
    <!-- ปุ่มเพิ่มสัตว์เลี้ยง ชี้ไปหน้าฝั่งสมาชิก (มี Upload รูป + custom_breed
         + ผูก user_id ให้แล้วในตัว) แทนที่จะทำฟอร์มแยกซ้ำซ้อนในแอดมิน —
         ถ้าอยากได้ฟอร์มเพิ่มสัตว์เลี้ยงแบบไม่มีเจ้าของ (เช่น สัตว์จากศูนย์พักพิง)
         อันนั้นมีอยู่แล้วที่เมนู "จัดการสัตว์เลี้ยง" ด้านซ้าย -->
    <a href="/index.php?page=create_pet" class="btn btn-secondary btn-sm" style="text-decoration: none;">+ เพิ่มสัตว์เลี้ยง (ฝั่งสมาชิก)</a>
  </div>
</div>
