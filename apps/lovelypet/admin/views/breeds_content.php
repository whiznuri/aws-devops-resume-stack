<!-- /admin/views/breeds_content.php -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h2 style="margin: 0; font-size: 1.6rem;">🐶 จัดการสายพันธุ์สัตว์เลี้ยง (Breed Management)</h2>
    <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 0.9rem;">
      เพิ่ม แก้ไข หรือลบข้อมูลสารานุกรมสายพันธุ์ (Pet Encyclopedia)
    </p>
  </div>

  <div style="display: flex; gap: 12px;">
    <!-- Form Search ยิงไปที่ dashboard.php?page=breeds -->
    <form method="get" action="/admin/dashboard.php" style="display: flex; gap: 8px;">
      <input type="hidden" name="page" value="breeds">
      <input type="text" name="search" value="<?= h($search ?? '') ?>" placeholder="ค้นหาชื่อสายพันธุ์..." style="padding: 8px 14px; border-radius: var(--radius-input); border: 1px solid #ccc; font-size: 0.9rem; width: 200px;">
      <button type="submit" class="btn btn-primary btn-sm">ค้นหา</button>
      <?php if (!empty($search)): ?>
        <a href="/admin/dashboard.php?page=breeds" class="btn btn-secondary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center;">ล้างค่า</a>
      <?php endif; ?>
    </form>
    <button type="button" class="btn btn-primary btn-sm" onclick="openBreedModal()">+ เพิ่มสายพันธุ์ใหม่</button>
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
          <th style="padding: 12px; width: 60px;">รูปภาพ</th>
          <th style="padding: 12px;">ชื่อสายพันธุ์</th>
          <th style="padding: 12px;">หมวดหมู่</th>
          <th style="padding: 12px;">รายละเอียดย่อ</th>
          <th style="padding: 12px; text-align: right;">การดำเนินการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($breeds)): ?>
          <?php foreach ($breeds as $b): ?>
            <tr style="border-bottom: 1px solid var(--bg-page);">
              <td style="padding: 12px;">
                <?php if (!empty($b['image_path'])): ?>
                  <img src="<?= h($b['image_path']) ?>" alt="<?= h($b['name']) ?>" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px;">
                <?php else: ?>
                  <div style="width: 48px; height: 48px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center;">🐾</div>
                <?php endif; ?>
              </td>
              <td style="padding: 12px; font-weight: 600;"><?= h($b['name']) ?></td>
              <td style="padding: 12px;">
                <span class="status-badge is-active" style="text-transform: uppercase;">
                  <?= h($b['category']) ?>
                </span>
              </td>
              <td style="padding: 12px; color: var(--text-muted); max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <?= h($b['summary'] ?? '-') ?>
              </td>
              <td style="padding: 12px; text-align: right;">
                <div style="display: inline-flex; gap: 8px;">
                  <button type="button" class="btn btn-secondary btn-sm" onclick='editBreed(<?= json_encode($b, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>แก้ไข</button>
                  
                  <!-- Form Delete ยิงเข้า dashboard.php?page=breeds -->
                  <form method="post" action="/admin/dashboard.php?page=breeds" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสายพันธุ์นี้?');" style="display: inline;">
                    <input type="hidden" name="action" value="delete_breed">
                    <input type="hidden" name="breed_id" value="<?= h((string)$b['id']) ?>">
                    <button type="submit" class="btn btn-sm" style="background: #f8d7da; color: #721c24; border: none; padding: 4px 10px; border-radius: var(--radius-input); font-size: 0.8rem; cursor: pointer;">ลบ</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" style="padding: 20px; text-align: center; color: var(--text-muted);">ไม่พบข้อมูลสายพันธุ์ในระบบ</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Dialog สำหรับเพิ่ม/แก้ไข -->
<dialog id="breedModal" style="border: none; border-radius: var(--radius-card); padding: 24px; width: 100%; max-width: 500px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
  <!-- Form Save/Edit ยิงเข้า dashboard.php?page=breeds -->
  <form method="post" action="/admin/dashboard.php?page=breeds" id="breedForm">
    <input type="hidden" name="action" value="save_breed">
    <input type="hidden" name="breed_id" id="modal_breed_id" value="0">
    
    <h3 id="modalTitle" style="margin-top: 0;">เพิ่มสายพันธุ์ใหม่</h3>
    
    <div style="margin-bottom: 12px;">
      <label style="display: block; margin-bottom: 4px; font-weight: 500;">ชื่อสายพันธุ์</label>
      <input type="text" name="name" id="modal_name" required style="width: 100%; padding: 8px 12px; border-radius: var(--radius-input); border: 1px solid #ccc; box-sizing: border-box;">
    </div>

    <div style="margin-bottom: 12px;">
      <label style="display: block; margin-bottom: 4px; font-weight: 500;">หมวดหมู่</label>
      <select name="category" id="modal_category" required style="width: 100%; padding: 8px 12px; border-radius: var(--radius-input); border: 1px solid #ccc; box-sizing: border-box;">
        <option value="dog">Dog (สุนัข)</option>
        <option value="cat">Cat (แมว)</option>
        <option value="other">Other (อื่นๆ)</option>
      </select>
    </div>

    <div style="margin-bottom: 12px;">
      <label style="display: block; margin-bottom: 4px; font-weight: 500;">URL รูปภาพ</label>
      <input type="url" name="image_path" id="modal_image_path" placeholder="https://..." style="width: 100%; padding: 8px 12px; border-radius: var(--radius-input); border: 1px solid #ccc; box-sizing: border-box;">
    </div>

    <div style="margin-bottom: 20px;">
      <label style="display: block; margin-bottom: 4px; font-weight: 500;">รายละเอียดโดยย่อ</label>
      <textarea name="summary" id="modal_summary" rows="3" style="width: 100%; padding: 8px 12px; border-radius: var(--radius-input); border: 1px solid #ccc; box-sizing: border-box;"></textarea>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 8px;">
      <button type="button" class="btn btn-secondary btn-sm" onclick="closeBreedModal()">ยกเลิก</button>
      <button type="submit" class="btn btn-primary btn-sm">บันทึกข้อมูล</button>
    </div>
  </form>
</dialog>

<script>
const modal = document.getElementById('breedModal');

function openBreedModal() {
  document.getElementById('modalTitle').innerText = 'เพิ่มสายพันธุ์ใหม่';
  document.getElementById('modal_breed_id').value = '0';
  document.getElementById('modal_name').value = '';
  document.getElementById('modal_category').value = 'dog';
  document.getElementById('modal_image_path').value = '';
  document.getElementById('modal_summary').value = '';
  modal.showModal();
}

function editBreed(data) {
  document.getElementById('modalTitle').innerText = 'แก้ไขสายพันธุ์ #' + data.id;
  document.getElementById('modal_breed_id').value = data.id;
  document.getElementById('modal_name').value = data.name || '';
  document.getElementById('modal_category').value = data.category || 'dog';
  document.getElementById('modal_image_path').value = data.image_path || '';
  document.getElementById('modal_summary').value = data.summary || '';
  modal.showModal();
}

function closeBreedModal() {
  modal.close();
}
</script>