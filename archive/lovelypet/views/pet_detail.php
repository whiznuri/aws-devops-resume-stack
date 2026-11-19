<?php
/**
 * views/pet_detail.php — Pet Detail View Component
 * Location: /views/pet_detail.php
 * Included by /index.php when ?page=pet_detail&id=N
 * Expects $pet (assoc array or null) from index.php's 'pet_detail' route.
 */
declare(strict_types=1);

$status_labels = ['available' => 'พร้อมรับเลี้ยง', 'reserved' => 'จองแล้ว', 'sold' => 'มีเจ้าของแล้ว'];
$gender_labels = ['male' => 'เพศผู้', 'female' => 'เพศเมีย'];
?>

<main class="container" style="padding-top: 40px; padding-bottom: 60px; max-width: 720px;">
  <?php if ($pet): ?>
    <div style="background: var(--bg-surface); border-radius: var(--radius-card); box-shadow: var(--shadow-card); overflow: hidden;">
      <div style="width: 100%; aspect-ratio: 4 / 3; background: #1a1a1a; position: relative; overflow: hidden;">
        <img src="<?= h($pet['image_path'] ?: 'https://via.placeholder.com/700x500?text=No+Image') ?>" alt="<?= h($pet['name']) ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; background: #fff;">
        <span class="status-badge is-active" style="position: absolute; top: 14px; right: 14px; text-transform: uppercase;">
          <?= h($status_labels[$pet['status']] ?? $pet['status']) ?>
        </span>
      </div>

      <div style="padding: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 8px; margin-bottom: 16px;">
          <h1 style="margin: 0; font-size: 1.8rem;"><?= h($pet['name']) ?></h1>
          <?php $already_liked = in_array((int)$pet['pet_id'], $_SESSION['liked_pets'] ?? [], true); ?>
          <form method="post" action="/index.php?page=like_pet" style="margin: 0;">
            <input type="hidden" name="pet_id" value="<?= (int)$pet['pet_id'] ?>">
            <input type="hidden" name="redirect_to" value="/index.php?page=pet_detail&id=<?= (int)$pet['pet_id'] ?>">
            <button type="submit" <?= $already_liked ? 'disabled' : '' ?> style="background: none; border: none; padding: 0; font: inherit; font-weight: 700; color: #e11d48; font-size: 1.2rem; cursor: <?= $already_liked ? 'default' : 'pointer' ?>; display: flex; align-items: center; gap: 6px;">
              <?= $already_liked ? '💖' : '❤️' ?> <?= (int)$pet['likes_count'] ?> Likes
            </button>
          </form>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; border-top: 1px solid var(--bg-page); padding-top: 20px;">
          <div>
            <span style="color: var(--text-muted); font-size: 0.82rem; display: block;">สายพันธุ์</span>
            <strong><?= h($pet['breed']) ?></strong>
          </div>
          <div>
            <span style="color: var(--text-muted); font-size: 0.82rem; display: block;">เพศ</span>
            <strong><?= h($gender_labels[$pet['gender']] ?? $pet['gender']) ?></strong>
          </div>
          <div>
            <span style="color: var(--text-muted); font-size: 0.82rem; display: block;">อายุ</span>
            <strong><?= (int)$pet['age_years'] ?> ปี</strong>
          </div>
          <div>
            <span style="color: var(--text-muted); font-size: 0.82rem; display: block;">ราคา</span>
            <strong>฿<?= number_format((float)$pet['price'], 2) ?></strong>
          </div>
          <?php if (!empty($pet['owner_name'])): ?>
          <div>
            <span style="color: var(--text-muted); font-size: 0.82rem; display: block;">เจ้าของ</span>
            <strong><?= h($pet['owner_name']) ?></strong>
          </div>
          <?php endif; ?>
        </div>

        <div style="margin-top: 28px;">
          <a href="/index.php?page=catalog" class="btn btn-secondary btn-sm">&larr; กลับไป Pet Catalog</a>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div style="text-align: center; padding: 60px 20px; background: var(--bg-surface); border-radius: var(--radius-card);">
      <div style="font-size: 3rem; margin-bottom: 12px;">🔍</div>
      <h3 style="margin: 0 0 8px;">ไม่พบสัตว์เลี้ยงตัวนี้</h3>
      <p style="color: var(--text-muted); margin: 0 0 20px;">อาจถูกลบไปแล้ว หรือลิงก์ไม่ถูกต้อง</p>
      <a href="/index.php?page=catalog" class="btn btn-primary btn-sm">ไปดู Pet Catalog</a>
    </div>
  <?php endif; ?>
</main>
