<!-- /admin/views/pet_types_content.php -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h2 style="margin: 0; font-size: 1.6rem;">🏷️ จัดการประเภทสัตว์เลี้ยง (Pet Type Management)</h2>
    <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 0.9rem;">
      หมวดหมู่ที่สมาชิกเลือกตอนสร้าง Pet Passport เช่น สุนัข / แมว / สัตว์เลี้ยงอื่นๆ
    </p>
  </div>

  <div style="display: flex; gap: 12px;">
    <!-- Form Search ยิงไปที่ dashboard.php?page=pet_types -->
    <form method="get" action="/admin/dashboard.php" style="display: flex; gap: 8px;">
      <input type="hidden" name="page" value="pet_types">
      <input type="text" name="search" value="<?= h($search ?? '') ?>" placeholder="ค้นหาชื่อประเภท..." style="padding: 8px 14px; border-radius: var(--radius-input); border: 1px solid #ccc; font-size: 0.9rem; width: 200px;">
      <button type="submit" class="btn btn-primary btn-sm">ค้นหา</button>
      <?php if (!empty($search)): ?>
        <a href="/admin/dashboard.php?page=pet_types" class="btn btn-secondary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center;">ล้างค่า</a>
      <?php endif; ?>
    </form>
    <button type="button" class="btn btn-primary btn-sm" onclick="openPetTypeModal()">+ เพิ่มประเภทใหม่</button>
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

<div style="background: var(--bg-surface); padding: 24px; border-radius: var(--radius-card); box-shadow: var(--shadow-card);">
  <div style="overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
      <thead>
        <tr style="border-bottom: 2px solid var(--bg-page); color: var(--text-muted);">
          <th style="padding: 12px; width: 80px;">ID</th>
          <th style="padding: 12px;">ชื่อประเภท</th>
          <th style="padding: 12px; text-align: right;">การดำเนินการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($pet_types)): ?>
          <?php foreach ($pet_types as $t): ?>
            <tr style="border-bottom: 1px solid var(--bg-page);">
              <td style="padding: 12px; font-weight: 600;">#<?= h((string)$t['id']) ?></td>
              <td style="padding: 12px;"><?= h($t['type_name']) ?></td>
              <td style="padding: 12px; text-align: right;">
                <div style="display: inline-flex; gap: 8px;">
                  <button type="button" class="btn btn-secondary btn-sm" onclick='editPetType(<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>แก้ไข</button>

                  <!-- Form Delete ยิงเข้า dashboard.php?page=pet_types -->
                  <form method="post" action="/admin/dashboard.php?page=pet_types" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบประเภทนี้?');" style="display: inline;">
                    <input type="hidden" name="action" value="delete_pet_type">
                    <input type="hidden" name="type_id" value="<?= h((string)$t['id']) ?>">
                    <button type="submit" class="btn btn-sm" style="background: #f8d7da; color: #721c24; border: none; padding: 4px 10px; border-radius: var(--radius-input); font-size: 0.8rem; cursor: pointer;">ลบ</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="3" style="padding: 20px; text-align: center; color: var(--text-muted);">ไม่พบข้อมูลประเภทสัตว์เลี้ยงในระบบ</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Dialog สำหรับเพิ่ม/แก้ไข -->
<dialog id="petTypeModal" style="border: none; border-radius: var(--radius-card); padding: 24px; width: 100%; max-width: 420px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
  <!-- Form Save/Edit ยิงเข้า dashboard.php?page=pet_types -->
  <form method="post" action="/admin/dashboard.php?page=pet_types" id="petTypeForm">
    <input type="hidden" name="action" value="save_pet_type">
    <input type="hidden" name="type_id" id="modal_type_id" value="0">

    <h3 id="petTypeModalTitle" style="margin-top: 0;">เพิ่มประเภทใหม่</h3>

    <div style="margin-bottom: 20px;">
      <label style="display: block; margin-bottom: 4px; font-weight: 500;">ชื่อประเภทสัตว์เลี้ยง</label>
      <input type="text" name="type_name" id="modal_type_name" required style="width: 100%; padding: 8px 12px; border-radius: var(--radius-input); border: 1px solid #ccc; box-sizing: border-box;">
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 8px;">
      <button type="button" class="btn btn-secondary btn-sm" onclick="closePetTypeModal()">ยกเลิก</button>
      <button type="submit" class="btn btn-primary btn-sm">บันทึกข้อมูล</button>
    </div>
  </form>
</dialog>

<script>
const petTypeModal = document.getElementById('petTypeModal');

function openPetTypeModal() {
  document.getElementById('petTypeModalTitle').innerText = 'เพิ่มประเภทใหม่';
  document.getElementById('modal_type_id').value = '0';
  document.getElementById('modal_type_name').value = '';
  petTypeModal.showModal();
}

function editPetType(data) {
  document.getElementById('petTypeModalTitle').innerText = 'แก้ไขประเภท #' + data.id;
  document.getElementById('modal_type_id').value = data.id;
  document.getElementById('modal_type_name').value = data.type_name || '';
  petTypeModal.showModal();
}

function closePetTypeModal() {
  petTypeModal.close();
}
</script>
