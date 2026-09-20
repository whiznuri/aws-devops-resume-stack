<?php
/**
 * views/create_pet.php — Create Pet Passport Form
 * Location: /views/create_pet.php
 * Included by /index.php when ?page=create_pet
 *
 * All validation, file-upload handling, and the DB insert already happened
 * in index.php's 'create_pet' route BEFORE header.php was included — this
 * file only renders the form + any $create_pet_errors. It must never call
 * header()/redirect itself: header.php has already printed HTML by the
 * time this file runs, so a redirect here would silently fail.
 *
 * Expects $all_breeds (array) and $create_pet_errors (array) in scope.
 */
declare(strict_types=1);

$old_breed_id = $_POST['breed_id'] ?? '';
$old_gender   = $_POST['gender'] ?? 'male';
$old_status   = $_POST['status'] ?? 'available';
?>

<main class="container" style="padding-top: 40px; padding-bottom: 60px; max-width: 640px;">
  <div class="section-heading" style="text-align: center;">
    <span class="status-badge is-active" style="margin-bottom: 12px; display: inline-block;">🐾 DIGITAL PET PASSPORT</span>
    <h1 style="font-size: 2rem; margin: 0 0 8px;">สร้าง Pet Passport ใหม่</h1>
    <p style="color: var(--text-muted); margin: 0;">กรอกข้อมูลสัตว์เลี้ยงของคุณให้ครบ แล้วอัปโหลดรูปสวยๆ สักใบ</p>
  </div>

  <?php if (!empty($create_pet_errors)): ?>
    <div style="background: #ffe6e6; color: #cc0000; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;">
      <ul style="margin: 0; padding-left: 20px;">
        <?php foreach ($create_pet_errors as $err): ?>
          <li><?= h($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="/index.php?page=create_pet" enctype="multipart/form-data" style="background: var(--bg-surface); padding: 28px; border-radius: var(--radius-card); box-shadow: var(--shadow-card); display: flex; flex-direction: column; gap: 16px;">

    <div>
      <label style="display: block; margin-bottom: 5px; font-weight: 600;">ชื่อสัตว์เลี้ยง</label>
      <input type="text" name="name" required value="<?= h($_POST['name'] ?? '') ?>" style="width: 100%; padding: 10px; border-radius: var(--radius-input); border: 1px solid #ccc;">
    </div>

    <div>
      <label style="display: block; margin-bottom: 5px; font-weight: 600;">สายพันธุ์</label>
      <select name="breed_id" id="breedSelect" style="width: 100%; padding: 10px; border-radius: var(--radius-input); border: 1px solid #ccc;">
        <option value="">-- พันธุ์ผสม / ไม่ระบุ --</option>
        <?php foreach ($all_breeds as $b): ?>
          <option value="<?= (int)$b['id'] ?>" <?= (string)$old_breed_id === (string)$b['id'] ? 'selected' : '' ?>><?= h($b['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div id="customBreedField">
      <label style="display: block; margin-bottom: 5px; font-weight: 600;">ระบุพันธุ์ผสม / อื่นๆ (ถ้ามี)</label>
      <input type="text" name="custom_breed" value="<?= h($_POST['custom_breed'] ?? '') ?>" placeholder="เช่น พันธุ์ทาง, ไม่ทราบสายพันธุ์" style="width: 100%; padding: 10px; border-radius: var(--radius-input); border: 1px solid #ccc;">
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
      <div>
        <label style="display: block; margin-bottom: 5px; font-weight: 600;">เพศ</label>
        <select name="gender" style="width: 100%; padding: 10px; border-radius: var(--radius-input); border: 1px solid #ccc;">
          <option value="male" <?= $old_gender === 'male' ? 'selected' : '' ?>>เพศผู้</option>
          <option value="female" <?= $old_gender === 'female' ? 'selected' : '' ?>>เพศเมีย</option>
        </select>
      </div>
      <div>
        <label style="display: block; margin-bottom: 5px; font-weight: 600;">อายุ (ปี)</label>
        <input type="number" name="age_years" min="0" max="50" value="<?= h($_POST['age_years'] ?? '0') ?>" style="width: 100%; padding: 10px; border-radius: var(--radius-input); border: 1px solid #ccc;">
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
      <div>
        <label style="display: block; margin-bottom: 5px; font-weight: 600;">ราคา (บาท)</label>
        <input type="number" name="price" min="0" step="0.01" value="<?= h($_POST['price'] ?? '0') ?>" style="width: 100%; padding: 10px; border-radius: var(--radius-input); border: 1px solid #ccc;">
      </div>
      <div>
        <label style="display: block; margin-bottom: 5px; font-weight: 600;">สถานะ</label>
        <select name="status" style="width: 100%; padding: 10px; border-radius: var(--radius-input); border: 1px solid #ccc;">
          <option value="available" <?= $old_status === 'available' ? 'selected' : '' ?>>พร้อมรับเลี้ยง</option>
          <option value="reserved" <?= $old_status === 'reserved' ? 'selected' : '' ?>>จองแล้ว</option>
          <option value="sold" <?= $old_status === 'sold' ? 'selected' : '' ?>>มีเจ้าของแล้ว</option>
        </select>
      </div>
    </div>

    <div>
      <label style="display: block; margin-bottom: 5px; font-weight: 600;">รูปภาพ (.jpg, .png, .webp — ไม่เกิน 2MB)</label>
      <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" style="width: 100%; padding: 8px; border-radius: var(--radius-input); border: 1px solid #ccc; background: #fff;">
    </div>

    <button type="submit" class="btn btn-primary" style="margin-top: 8px;">สร้าง Pet Passport</button>
  </form>
</main>

<script>
  // ซ่อนช่อง custom_breed อัตโนมัติถ้าเลือกสายพันธุ์จาก dropdown แล้ว (ช่วยให้ฟอร์มดูสะอาดขึ้น เฉยๆ ไม่ได้บังคับฝั่ง server)
  var breedSelect = document.getElementById('breedSelect');
  var customBreedField = document.getElementById('customBreedField');
  function toggleCustomBreed() {
    customBreedField.style.display = breedSelect.value === '' ? 'block' : 'none';
  }
  breedSelect.addEventListener('change', toggleCustomBreed);
  toggleCustomBreed();
</script>
