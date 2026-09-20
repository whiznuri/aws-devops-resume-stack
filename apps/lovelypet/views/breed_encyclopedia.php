<?php
/**
 * views/breed_encyclopedia_content.php — Breed Encyclopedia View Component
 * Location: /views/breed_encyclopedia_content.php
 * Included by /index.php when ?page=encyclopedia
 */
declare(strict_types=1);
?>

<main class="container" style="padding-top: 40px; padding-bottom: 60px;">
  
  <!-- Page Header -->
  <div style="text-align: center; max-width: 650px; margin: 0 auto 36px;">
    <span class="status-badge is-active" style="margin-bottom: 12px; display: inline-block;">📚 PET ENCYCLOPEDIA</span>
    <h1 style="font-size: 2.2rem; margin: 0 0 12px; font-weight: 800;">สำรวจสารานุกรมสายพันธุ์สัตว์เลี้ยง</h1>
    <p style="color: var(--text-muted); font-size: 1rem; margin: 0;">
      ค้นคว้าข้อมูล ลักษณะนิสัย และการดูแลสายพันธุ์สุนัข แมว และสัตว์เลี้ยงอื่นๆ เพื่อเตรียมความพร้อมก่อนต้อนรับสมาชิกใหม่
    </p>
  </div>

  <!-- Filter & Search Controls -->
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 32px; background: var(--bg-surface); padding: 16px 24px; border-radius: var(--radius-card); box-shadow: var(--shadow-card);">
    
    <!-- Category Tabs -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
      <a href="/index.php?page=encyclopedia&cat=all<?= !empty($search) ? '&q='.urlencode($search) : '' ?>" class="btn btn-sm <?= ($category ?? 'all') === 'all' ? 'btn-primary' : 'btn-secondary' ?>">
        ทั้งหมด
      </a>
      <a href="/index.php?page=encyclopedia&cat=dog<?= !empty($search) ? '&q='.urlencode($search) : '' ?>" class="btn btn-sm <?= ($category ?? '') === 'dog' ? 'btn-primary' : 'btn-secondary' ?>">
        🐶 สุนัข (Dogs)
      </a>
      <a href="/index.php?page=encyclopedia&cat=cat<?= !empty($search) ? '&q='.urlencode($search) : '' ?>" class="btn btn-sm <?= ($category ?? '') === 'cat' ? 'btn-primary' : 'btn-secondary' ?>">
        🐱 แมว (Cats)
      </a>
      <a href="/index.php?page=encyclopedia&cat=other<?= !empty($search) ? '&q='.urlencode($search) : '' ?>" class="btn btn-sm <?= ($category ?? '') === 'other' ? 'btn-primary' : 'btn-secondary' ?>">
        🐰 สัตว์อื่นๆ (Others)
      </a>
    </div>

    <!-- Search Form -->
    <form method="get" action="/index.php" style="display: flex; gap: 8px;">
      <input type="hidden" name="page" value="encyclopedia">
      <?php if (($category ?? 'all') !== 'all'): ?>
        <input type="hidden" name="cat" value="<?= h($category) ?>">
      <?php endif; ?>
      <input type="text" name="q" value="<?= h($search ?? '') ?>" placeholder="ค้นหาชื่อสายพันธุ์..." style="padding: 8px 14px; border-radius: var(--radius-input); border: 1px solid #ccc; font-size: 0.9rem; width: 220px;">
      <button type="submit" class="btn btn-primary btn-sm">ค้นหา</button>
      <?php if (!empty($search)): ?>
        <a href="/index.php?page=encyclopedia&cat=<?= h($category ?? 'all') ?>" class="btn btn-secondary btn-sm" style="text-decoration: none;">ล้าง</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Cards Grid Layout -->
  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
    <?php if (!empty($breeds)): ?>
      <?php foreach ($breeds as $b): ?>
        <div style="background: var(--bg-surface); border-radius: var(--radius-card); overflow: hidden; box-shadow: var(--shadow-card); transition: transform 0.2s ease, box-shadow 0.2s ease; display: flex; flex-direction: column;" class="breed-card">
          
          <!-- Container กรอบรูปภาพ -->
<div style="height: 200px; width: 100%; position: relative; overflow: hidden; background: #1a1a1a;">
  <?php if (!empty($b['image_path'])): ?>
    <!-- 1. ภาพพื้นหลังเบลอ (ช่วยขยายให้เต็มกรอบเนียนๆ) -->
    <img src="<?= h($b['image_path']) ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; filter: blur(10px) brightness(0.7); transform: scale(1.1);">
    
    <!-- 2. ภาพหลักด้านหน้า (แสดงเต็มตัว 100% ไม่โดนตัดหัวตัดหูแน่นอน) -->
    <img src="<?= h($b['image_path']) ?>" alt="<?= h($b['name']) ?>" style="position: relative; width: 100%; height: 100%; object-fit: contain; z-index: 1;">
  <?php else: ?>
    <div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 3rem;">🐾</div>
  <?php endif; ?>

  <span class="status-badge is-active" style="position: absolute; top: 12px; right: 12px; text-transform: uppercase; background: rgba(255,255,255,0.9); backdrop-filter: blur(4px); z-index: 2;">
    <?= h($b['category']) ?>
  </span>
</div>

          <div style="padding: 20px; display: flex; flex-direction: column; flex-grow: 1;">
            <h3 style="margin: 0 0 8px; font-size: 1.25rem; font-weight: 700;"><?= h($b['name']) ?></h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0 0 16px; line-height: 1.5; flex-grow: 1; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
              <?= h($b['summary'] ?? 'ไม่มีข้อมูลคำอธิบายย่อ') ?>
            </p>
            <button type="button" class="btn btn-secondary btn-sm" style="width: 100%; margin-top: auto;" onclick='showBreedDetail(<?= json_encode($b, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
              📖 อ่านรายละเอียดเพิ่มเติม
            </button>
          </div>

        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: var(--bg-surface); border-radius: var(--radius-card);">
        <div style="font-size: 3rem; margin-bottom: 12px;">🔍</div>
        <h3 style="margin: 0 0 8px;">ไม่พบข้อมูลสายพันธุ์</h3>
        <p style="color: var(--text-muted); margin: 0;">ลองเปลี่ยนคำค้นหา หรือสลับหมวดหมู่ดูนะครับ</p>
      </div>
    <?php endif; ?>
  </div>

</main>

<!-- Modal แสดงรายละเอียดสายพันธุ์ -->
<dialog id="detailModal" style="border: none; border-radius: var(--radius-card); padding: 0; width: 100%; max-width: 550px; box-shadow: 0 15px 30px rgba(0,0,0,0.25); overflow: hidden;">
  <!-- รูปภาพใน Modal (หลังคลิก) — แสดงภาพเต็มตัว ไม่โดนตัดขอบ -->
  <div style="position: relative; background: var(--bg-page, #f4f6f8); display: flex; align-items: center; justify-content: center; min-height: 260px;">
    <img id="detailImg" src="" style="max-width: 100%; max-height: 320px; object-fit: contain; display: block; padding: 12px;">
    <button type="button" onclick="document.getElementById('detailModal').close()" style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.5); color: #fff; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1.1rem; z-index: 2;">✕</button>
  </div>
  <div style="padding: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
      <h2 id="detailTitle" style="margin: 0; font-size: 1.5rem;"></h2>
      <span id="detailCategory" class="status-badge is-active" style="text-transform: uppercase;"></span>
    </div>
    <div style="border-top: 1px solid var(--bg-page); padding-top: 12px; margin-top: 12px;">
      <h4 style="margin: 0 0 6px; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">รายละเอียด / คำแนะนำการดูแล</h4>
      <p id="detailSummary" style="margin: 0; line-height: 1.6; color: var(--text-color); font-size: 0.95rem;"></p>
    </div>
    <div style="margin-top: 24px; text-align: right;">
      <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('detailModal').close()">ปิดหน้าต่าง</button>
    </div>
  </div>
</dialog>

<script>
function showBreedDetail(data) {
  document.getElementById('detailTitle').innerText = data.name || '';
  document.getElementById('detailCategory').innerText = data.category || '';
  document.getElementById('detailSummary').innerText = data.summary || 'ไม่มีข้อมูลรายละเอียดเพิ่มเติม';
  document.getElementById('detailImg').src = data.image_path || 'https://via.placeholder.com/600x300?text=No+Image';
  document.getElementById('detailModal').showModal();
}
</script>