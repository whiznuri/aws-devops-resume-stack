<?php
/**
 * views/pet_catalog.php — Pet Catalog View Component
 * Location: /views/pet_catalog.php
 * Included by /index.php when ?page=catalog
 * Expects $pets, $filter_status, $page_num, $total_pages, $total_pets from
 * index.php's 'catalog' route — this view only renders, no querying here.
 */
declare(strict_types=1);

$status_labels = [
    'all'       => 'ทั้งหมด',
    'available' => 'พร้อมรับเลี้ยง',
    'reserved'  => 'จองแล้ว',
    'sold'      => 'มีเจ้าของแล้ว',
];
?>

<main class="container" style="padding-top: 40px; padding-bottom: 60px;">

  <!-- Page Header -->
  <div style="text-align: center; max-width: 650px; margin: 0 auto 36px;">
    <span class="status-badge is-active" style="margin-bottom: 12px; display: inline-block;">🐾 PET CATALOG</span>
    <h1 style="font-size: 2.2rem; margin: 0 0 12px; font-weight: 800;">สัตว์เลี้ยงทั้งหมดในระบบ</h1>
    <p style="color: var(--text-muted); font-size: 1rem; margin: 0;">
      Digital Pet Passport ของสมาชิกทุกคน — กด "View Profile" เพื่อดูรายละเอียดเต็มๆ ของแต่ละตัว
    </p>
  </div>

  <!-- Status Filter -->
  <div style="display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; margin-bottom: 32px;">
    <?php foreach ($status_labels as $key => $label): ?>
      <a href="/index.php?page=catalog&status=<?= h($key) ?>" class="btn btn-sm <?= $filter_status === $key ? 'btn-primary' : 'btn-secondary' ?>">
        <?= h($label) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($pets)): ?>
    <div class="pet-grid">
      <?php foreach ($pets as $pet): ?>
        <article class="pet-card">
          <div class="pet-image-wrap">
            <img src="<?= h($pet['image_path'] ?: 'https://via.placeholder.com/400x400?text=No+Image') ?>" alt="<?= h($pet['name']) ?>" loading="lazy">
            <?php $already_liked_grid = in_array((int)$pet['pet_id'], $_SESSION['liked_pets'] ?? [], true); ?>
            <form method="post" action="/index.php?page=like_pet" class="like-badge" style="margin: 0;">
              <input type="hidden" name="pet_id" value="<?= (int)$pet['pet_id'] ?>">
              <input type="hidden" name="redirect_to" value="/index.php?page=catalog&status=<?= h($filter_status) ?>&p=<?= $page_num ?>">
              <button type="submit" <?= $already_liked_grid ? 'disabled' : '' ?> style="background: none; border: none; padding: 0; margin: 0; font: inherit; color: inherit; display: flex; align-items: center; gap: 4px; cursor: <?= $already_liked_grid ? 'default' : 'pointer' ?>;">
                &#10084; <?= (int)$pet['likes_count'] ?>
              </button>
            </form>
          </div>
          <div class="pet-body">
            <h3><?= h($pet['name']) ?></h3>
            <span class="breed-badge"><?= h($pet['breed']) ?></span>
            <button type="button" class="btn btn-secondary btn-sm" style="width: 100%;" onclick='showPetDetail(<?= json_encode($pet, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
              View Profile
            </button>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <div style="display: flex; justify-content: center; gap: 8px; margin-top: 36px; flex-wrap: wrap;">
        <?php if ($page_num > 1): ?>
          <a href="/index.php?page=catalog&status=<?= h($filter_status) ?>&p=<?= $page_num - 1 ?>" class="btn btn-sm btn-secondary">&laquo; ก่อนหน้า</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <a href="/index.php?page=catalog&status=<?= h($filter_status) ?>&p=<?= $i ?>" class="btn btn-sm <?= $i === $page_num ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page_num < $total_pages): ?>
          <a href="/index.php?page=catalog&status=<?= h($filter_status) ?>&p=<?= $page_num + 1 ?>" class="btn btn-sm btn-secondary">ถัดไป &raquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <div style="text-align: center; padding: 60px 20px; background: var(--bg-surface); border-radius: var(--radius-card);">
      <div style="font-size: 3rem; margin-bottom: 12px;">🐾</div>
      <h3 style="margin: 0 0 8px;">ยังไม่มีสัตว์เลี้ยงในหมวดนี้</h3>
      <p style="color: var(--text-muted); margin: 0;">ลองเปลี่ยนตัวกรอง หรือเป็นคนแรกที่สร้าง Pet Passport!</p>
    </div>
  <?php endif; ?>

</main>

<!-- Modal แสดงรายละเอียดสัตว์เลี้ยง (แพทเทิร์นเดียวกับ Modal ใน breed_encyclopedia.php) -->
<dialog id="petDetailModal" style="border: none; border-radius: var(--radius-card); padding: 0; width: 100%; max-width: 550px; box-shadow: 0 15px 30px rgba(0,0,0,0.25); overflow: hidden;">
  <div style="position: relative; background: var(--bg-page, #f4f6f8); display: flex; align-items: center; justify-content: center; min-height: 260px;">
    <img id="petDetailImg" src="" style="max-width: 100%; max-height: 320px; object-fit: contain; display: block; padding: 12px;">
    <button type="button" onclick="document.getElementById('petDetailModal').close()" style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.5); color: #fff; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1.1rem; z-index: 2;">✕</button>
  </div>
  <div style="padding: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
      <h2 id="petDetailName" style="margin: 0; font-size: 1.5rem;"></h2>
      <span id="petDetailStatus" class="status-badge is-active" style="text-transform: uppercase;"></span>
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.92rem; border-top: 1px solid var(--bg-page); padding-top: 14px;">
      <div><strong>สายพันธุ์:</strong> <span id="petDetailBreed"></span></div>
      <div><strong>เพศ:</strong> <span id="petDetailGender"></span></div>
      <div><strong>อายุ:</strong> <span id="petDetailAge"></span> ปี</div>
      <div><strong>ราคา:</strong> ฿<span id="petDetailPrice"></span></div>
      <div><strong>ไลก์:</strong> ❤️ <span id="petDetailLikes"></span></div>
    </div>
    <div style="margin-top: 24px; text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
      <a id="petDetailFullLink" href="#" class="btn btn-secondary btn-sm">เปิดหน้าเต็ม</a>
      <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('petDetailModal').close()">ปิดหน้าต่าง</button>
    </div>
  </div>
</dialog>

<script>
const petStatusLabels = { available: 'พร้อมรับเลี้ยง', reserved: 'จองแล้ว', sold: 'มีเจ้าของแล้ว' };
const petGenderLabels = { male: 'เพศผู้', female: 'เพศเมีย' };

function showPetDetail(data) {
  document.getElementById('petDetailName').innerText = data.name || '';
  document.getElementById('petDetailStatus').innerText = petStatusLabels[data.status] || data.status || '';
  document.getElementById('petDetailBreed').innerText = data.breed || 'ไม่ระบุสายพันธุ์';
  document.getElementById('petDetailGender').innerText = petGenderLabels[data.gender] || data.gender || '-';
  document.getElementById('petDetailAge').innerText = (data.age_years ?? '-');
  document.getElementById('petDetailPrice').innerText = Number(data.price || 0).toLocaleString();
  document.getElementById('petDetailLikes').innerText = data.likes_count ?? 0;
  document.getElementById('petDetailImg').src = data.image_path || 'https://via.placeholder.com/600x400?text=No+Image';
  document.getElementById('petDetailFullLink').href = '/index.php?page=pet_detail&id=' + encodeURIComponent(data.pet_id);
  document.getElementById('petDetailModal').showModal();
}
</script>
