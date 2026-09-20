<!-- /admin/views/pets_content.php -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h2 style="margin: 0; font-size: 1.6rem;">🐾 จัดการสัตว์เลี้ยง (Pet Management)</h2>
    <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 0.9rem;">
      เพิ่ม แก้ไข หรือจัดการสถานะสัตว์เลี้ยงในระบบ
    </p>
  </div>

  <div style="display: flex; gap: 12px;">
    <!-- Form Search ยิงไปที่ dashboard.php?page=pets -->
    <form method="get" action="/admin/dashboard.php" style="display: flex; gap: 8px;">
      <input type="hidden" name="page" value="pets">
      <input type="text" name="search" value="<?= h($search ?? '') ?>" placeholder="ค้นหาชื่อสัตว์เลี้ยง หรือสายพันธุ์..." style="padding: 8px 14px; border-radius: var(--radius-input); border: 1px solid #ccc; font-size: 0.9rem; width: 220px;">
      <button type="submit" class="btn btn-primary btn-sm">ค้นหา</button>
      <?php if (!empty($search)): ?>
        <a href="/admin/dashboard.php?page=pets" class="btn btn-secondary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center;">ล้างค่า</a>
      <?php endif; ?>
    </form>
    <button type="button" class="btn btn-primary btn-sm" onclick="openPetModal()">+ เพิ่มสัตว์เลี้ยง</button>
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
          <th style="padding: 12px;">ชื่อสัตว์เลี้ยง</th>
          <th style="padding: 12px;">สายพันธุ์</th>
          <th style="padding: 12px;">อายุ / เพศ</th>
          <th style="padding: 12px;">ราคา (บาท)</th>
          <th style="padding: 12px;">สถานะ</th>
          <th style="padding: 12px; text-align: right;">การดำเนินการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($pets)): ?>
          <?php foreach ($pets as $p): ?>
            <tr style="border-bottom: 1px solid var(--bg-page);">
              <td style="padding: 12px;">
                <?php if (!empty($p['image_path'])): ?>
                  <img src="<?= h($p['image_path']) ?>" alt="<?= h($p['name']) ?>" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px;">
                <?php else: ?>
                  <div style="width: 48px; height: 48px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center;">🐾</div>
                <?php endif; ?>
              </td>
              <td style="padding: 12px; font-weight: 600;"><?= h($p['name']) ?></td>
              <td style="padding: 12px; color: var(--text-muted);"><?= h($p['breed_name'] ?? 'ไม่ระบุ') ?></td>
              <td style="padding: 12px;">
                <?= (int)($p['age_years'] ?? 0) ?> ปี 
                <span style="font-size: 0.8rem; color: var(--text-muted);"> (<?= $p['gender'] === 'female' ? '♀️ เมีย' : '♂️ ผู้' ?>)</span>
              </td>
              <td style="padding: 12px; font-weight: 600; color: #2b6cb0;">
                ฿<?= number_format((float)($p['price'] ?? 0), 2) ?>
              </td>
              <td style="padding: 12px;">
                <?php 
                  $st = $p['status'] ?? 'available';
                  $badge = ($st === 'available') ? 'is-active' : (($st === 'reserved') ? 'is-pending' : 'is-blocked');
                ?>
                <span class="status-badge <?= $badge ?>" style="text-transform: uppercase;">
                  <?= h($st) ?>
                </span>
              </td>
              <td style="padding: 12px; text-align: right;">
                <div style="display: inline-flex; gap: 8px;">
                  <button type="button" class="btn btn-secondary btn-sm" onclick='editPet(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>แก้ไข</button>
                  
                  <form method="post" action="/admin/dashboard.php?page=pets" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?');" style="display: inline;">
                    <input type="hidden" name="action" value="delete_pet">
                    <input type="hidden" name="pet_id" value="<?= h((string)$p['id']) ?>">
                    <button type="submit" class="btn btn-sm" style="background: #f8d7da; color: #721c24; border: none; padding: 4px 10px; border-radius: var(--radius-input); font-size: 0.8rem; cursor: pointer;">ลบ</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" style="padding: 20px; text-align: center; color: var(--text-muted);">ไม่พบข้อมูลสัตว์เลี้ยงในระบบ</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Dialog เพิ่ม/แก้ไข สัตว์เลี้ยง -->
<dialog id="petModal" style="border: none; border-radius: var(--radius-card); padding: 24px; width: 100%; max-width: 500px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
  <form method="post" action="/admin/dashboard.php?page=pets" id="petForm">
    <input type="hidden" name="action" value="save_pet">
    <input type="hidden" name="pet_id" id="modal_pet_id" value="0">
    
    <h3 id="petModalTitle" style="margin-top: 0;">เพิ่มสัตว์เลี้ยงใหม่</h3>
    
    <div style="margin-bottom: 12px;">
      <label style="display: block; margin-bottom: 4px; font-weight: 500;">ชื่อสัตว์เลี้ยง</label>
      <input type="text" name="name" id="modal_pet_name" required style="width: 100%; padding: 8px 12px; border-radius: var(--radius-input); border: 1px solid #ccc; box-sizing: border-box;">
    </div>

    <div style="margin-bottom: 12px;">
      <label style="display: block; margin-bottom: 4px; font-weight: 500;">สายพันธุ์</label>
      <select name="breed_id" id="modal_breed_id" required style="width: 100%; padding: 8px 12px; border-radius: var(--radius-input); border: 1px solid #ccc; box-sizing: border-box;">
        <option value="">-- เลือกสายพันธุ์ --</option>
        <?php foreach (($breeds ?? []) as $b): ?>
          <option value="<?= $b['id'] ?>"><?= h($b['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
      <div>
        <label style="display: block; margin-bottom: 4px; font-weight: 500;">อายุ (ปี)</label>
        <input type="number" name="age_years" id="modal_age_years" min="0" value="0" style="width: 100%; padding: 8px 12px; border-radius: var(--radius-input); border: 1px solid #ccc; box-sizing: border-box;">
      </div>
      <div>
        <label style="display: block; margin-bottom: 4px; font-weight: 500;">เพศ</label>
        <select name="gender" id="modal_gender" style="width: 100%; padding: 8px 12px; border-radius: var(--radius-input); border: 1px solid #ccc; box-sizing: border-box;">
          <option value="male">ตัวผู้ (Male)</option>
          <option value="female">ตัวเมีย (Female)</option>
        </select>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
      <div>
        <label style="display: block; margin-bottom: 4px; font-weight: 500;">ราคา (บาท)</label>
        <input type="number" step="0.01" name="price" id="modal_price" value="0.00" style="width: 100%; padding: 8px 12px; border-radius: var(--radius-input); border: 1px solid #ccc; box-sizing: border-box;">
      </div>
      <div>
        <label style="display: block; margin-bottom: 4px; font-weight: 500;">สถานะ</label>
        <select name="status" id="modal_status" style="width: 100%; padding: 8px 12px; border-radius: var(--radius-input); border: 1px solid #ccc; box-sizing: border-box;">
          <option value="available">พร้อมย้ายบ้าน (Available)</option>
          <option value="reserved">ติดจอง (Reserved)</option>
          <option value="sold">ย้ายบ้านแล้ว (Sold)</option>
        </select>
      </div>
    </div>

    <div style="margin-bottom: 20px;">
      <label style="display: block; margin-bottom: 4px; font-weight: 500;">URL รูปภาพ</label>
      <input type="url" name="image_path" id="modal_pet_image_path" placeholder="https://..." style="width: 100%; padding: 8px 12px; border-radius: var(--radius-input); border: 1px solid #ccc; box-sizing: border-box;">
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 8px;">
      <button type="button" class="btn btn-secondary btn-sm" onclick="closePetModal()">ยกเลิก</button>
      <button type="submit" class="btn btn-primary btn-sm">บันทึกข้อมูล</button>
    </div>
  </form>
</dialog>

<script>
const petModal = document.getElementById('petModal');

function openPetModal() {
  document.getElementById('petModalTitle').innerText = 'เพิ่มสัตว์เลี้ยงใหม่';
  document.getElementById('modal_pet_id').value = '0';
  document.getElementById('modal_pet_name').value = '';
  document.getElementById('modal_breed_id').value = '';
  document.getElementById('modal_age_years').value = '0';
  document.getElementById('modal_gender').value = 'male';
  document.getElementById('modal_price').value = '0.00';
  document.getElementById('modal_status').value = 'available';
  document.getElementById('modal_pet_image_path').value = '';
  petModal.showModal();
}

function editPet(data) {
  document.getElementById('petModalTitle').innerText = 'แก้ไขสัตว์เลี้ยง #' + data.id;
  document.getElementById('modal_pet_id').value = data.id;
  document.getElementById('modal_pet_name').value = data.name || '';
  document.getElementById('modal_breed_id').value = data.breed_id || '';
  document.getElementById('modal_age_years').value = data.age_years || '0';
  document.getElementById('modal_gender').value = data.gender || 'male';
  document.getElementById('modal_price').value = data.price || '0.00';
  document.getElementById('modal_status').value = data.status || 'available';
  document.getElementById('modal_pet_image_path').value = data.image_path || '';
  petModal.showModal();
}

function closePetModal() {
  petModal.close();
}
</script>