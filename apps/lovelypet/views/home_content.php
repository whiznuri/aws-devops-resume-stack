<?php
/**
 * views/home_content.php — Homepage Body Sections
 * Location: /views/home_content.php
 * Included by /index.php when ?page=home (the default route).
 * index.php already queries the DB before requiring this file, so it
 * expects $breeds, $pets, $leaderboard (arrays, possibly empty) and the
 * h() helper to already be in scope — this view does no querying itself.
 */
declare(strict_types=1);

$is_logged_in = isset($_SESSION['member_name']);

// การ์ด Passport บน Hero: โชว์สัตว์เลี้ยงที่มีคนกด Like เยอะที่สุด (ตัวแรกใน
// $leaderboard ซึ่ง query มาเรียง like_count DESC อยู่แล้ว) ถ้ายังไม่มีเลย
// ก็ fallback ไปที่ตัวแรกใน $pets แทน เดิมส่วนนี้ hardcode ชื่อ "Mochi" /
// "Golden Retriever" / 128 ไว้ตรงๆ ใน HTML ไม่ได้ผูกกับข้อมูลจริงเลย
$hero_pet = $leaderboard[0] ?? ($pets[0] ?? null);
?>

<!-- 2. Hero Banner -->
<section class="hero">
  <div class="container">
    <div class="hero-copy">
      <span class="eyebrow-passport">&#128062; Digital Pet Passport</span>
      <h1>Give your pet a passport, and a place in the community.</h1>
      <p>Search breed knowledge, follow other pet parents, and create a digital passport for your own pet — complete with a profile the whole community can like.</p>
      <div class="hero-cta">
        <a href="/index.php?page=encyclopedia" class="btn btn-secondary">Explore Breed Encyclopedia</a>
        <?php if ($is_logged_in): ?>
          <!-- ล็อกอินแล้ว: มีหน้า create_pet จริงแล้ว พาไปสร้าง Passport ได้เลย -->
          <a href="/index.php?page=create_pet" class="btn btn-primary">Create Pet Passport</a>
        <?php else: ?>
          <!-- ยังไม่ล็อกอิน: ต้องล็อกอินก่อนถึงจะสร้าง Passport ได้ -->
          <a href="/index.php?page=login" class="btn btn-primary">Create Pet Passport</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="hero-passport-card">
      <?php if ($hero_pet): ?>
        <img src="<?= h($hero_pet['image_path']) ?>" alt="<?= h($hero_pet['name']) ?>">
        <div class="passport-row"><span>Name</span><strong><?= h($hero_pet['name']) ?></strong></div>
        <div class="passport-row"><span>Breed</span><strong><?= h($hero_pet['breed'] ?? 'ไม่ระบุสายพันธุ์') ?></strong></div>
        <div class="passport-row"><span>Likes</span><strong>&#10084; <?= (int)$hero_pet['likes_count'] ?></strong></div>
      <?php else: ?>
        <div style="text-align:center; padding: 36px 12px; color: var(--text-muted);">
          <p style="margin:0;">ยังไม่มีสัตว์เลี้ยงในระบบ — เป็นคนแรกที่สร้าง Pet Passport สิ!</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- 3. Pet Encyclopedia Zone -->
<section class="section">
  <div class="container">
    <div class="section-heading">
      <h2>Breed Encyclopedia</h2>
      <p>Quick knowledge cards on temperament and care, before you commit to a breed.</p>
    </div>

    <?php if (!empty($breeds)): ?>
      <div class="breed-grid">
        <?php foreach ($breeds as $breed): ?>
          <article class="breed-card" data-category="<?= h($breed['category']) ?>">
            <img src="<?= h($breed['image_path']) ?>" alt="<?= h($breed['name']) ?>" loading="lazy">
            <div class="breed-body">
              <span class="breed-category"><?= h(ucfirst($breed['category'])) ?></span>
              <h3><?= h($breed['name']) ?></h3>
              <p><?= h($breed['summary']) ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div style="text-align:center; margin-top: 24px;">
        <a href="/index.php?page=encyclopedia" class="btn btn-secondary btn-sm">ดูสายพันธุ์ทั้งหมด</a>
      </div>
    <?php else: ?>
      <p style="text-align:center; color: var(--text-muted);">ยังไม่มีข้อมูลสายพันธุ์ในระบบ</p>
    <?php endif; ?>
  </div>
</section>

<!-- 4. Community Pet Catalog Grid -->
<section class="section section-alt">
  <div class="container">
    <div class="section-heading">
      <h2>Community Pet Catalog</h2>
      <p>Digital passports created by fellow pet parents. Give a pet a &#10084; if you love its profile.</p>
    </div>

    <?php if (!empty($pets)): ?>
      <div class="pet-grid">
        <?php foreach ($pets as $pet): ?>
          <article class="pet-card">
            <div class="pet-image-wrap">
              <img src="<?= h($pet['image_path']) ?>" alt="<?= h($pet['name']) ?>" loading="lazy">
              <span class="like-badge">&#10084; <?= (int)$pet['likes_count'] ?></span>
            </div>
            <div class="pet-body">
              <h3><?= h($pet['name']) ?></h3>
              <span class="breed-badge"><?= h($pet['breed'] ?? 'ไม่ระบุสายพันธุ์') ?></span>
              <a href="/index.php?page=pet_detail&id=<?= (int)$pet['pet_id'] ?>" class="btn btn-secondary btn-sm">View Profile</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div style="text-align:center; margin-top: 24px;">
        <a href="/index.php?page=catalog" class="btn btn-secondary btn-sm">ดูสัตว์เลี้ยงทั้งหมด</a>
      </div>
    <?php else: ?>
      <p style="text-align:center; color: var(--text-muted);">ยังไม่มีสัตว์เลี้ยงในระบบ</p>
    <?php endif; ?>
  </div>
</section>

<!-- 5. Leaderboard Top 3 -->
<section class="section">
  <div class="container">
    <div class="section-heading">
      <h2>Leaderboard — Top 3 Loved Pets</h2>
      <p>Ranked by community hearts this season.</p>
    </div>

    <?php if (!empty($leaderboard)): ?>
      <div class="leaderboard-grid">
        <?php
        $rank_labels = [1 => 'Gold', 2 => 'Silver', 3 => 'Bronze'];
        foreach (array_slice($leaderboard, 0, 3) as $index => $pet):
            $rank = $index + 1;
        ?>
          <div class="rank-card rank-<?= $rank ?>">
            <span class="rank-number"><span>#<?= $rank ?></span></span>
            <img src="<?= h($pet['image_path']) ?>" alt="<?= h($pet['name']) ?>">
            <h3><?= h($pet['name']) ?></h3>
            <p><?= h($pet['breed'] ?? 'ไม่ระบุสายพันธุ์') ?></p>
            <div class="likes">&#10084; <?= (int)$pet['likes_count'] ?> — <?= h($rank_labels[$rank] ?? '') ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div style="text-align:center; margin-top: 24px;">
        <a href="/index.php?page=leaderboard" class="btn btn-secondary btn-sm">ดู Leaderboard เต็ม</a>
      </div>
    <?php else: ?>
      <p style="text-align:center; color: var(--text-muted);">ยังไม่มีสัตว์เลี้ยงในระบบ</p>
    <?php endif; ?>
  </div>
</section>
