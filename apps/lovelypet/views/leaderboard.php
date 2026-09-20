<?php
/**
 * views/leaderboard_content.php — Leaderboard View Component
 * Location: /views/leaderboard_content.php
 * Included by /index.php when ?page=leaderboard
 */
declare(strict_types=1);

// แยก Top 3 ออกมาจากรายการทั้งหมด
$top_three = array_slice($leaderboard ?? [], 0, 3);
$other_ranks = array_slice($leaderboard ?? [], 3);
$rank_badges = [1 => '🥇 Gold', 2 => '🥈 Silver', 3 => '🥉 Bronze'];
?>

<main class="container" style="padding-top: 40px; padding-bottom: 60px;">
  
  <!-- Page Header -->
  <div style="text-align: center; max-width: 650px; margin: 0 auto 36px;">
    <span class="status-badge is-active" style="margin-bottom: 12px; display: inline-block;">🏆 MOST LOVED PETS</span>
    <h1 style="font-size: 2.2rem; margin: 0 0 12px; font-weight: 800;">Leaderboard สัตว์เลี้ยงยอดนิยม</h1>
    <p style="color: var(--text-muted); font-size: 1rem; margin: 0;">
      อันดับสัตว์เลี้ยงขวัญใจชุมชนที่ได้รับหัวใจ ❤️ มากที่สุดในซีซั่นนี้
    </p>
  </div>

  <!-- Top 3 Loved Pets Showcase -->
  <?php if (!empty($top_three)): ?>
    <div class="leaderboard-grid" style="margin-bottom: 48px;">
      <?php foreach ($top_three as $index => $pet): 
        $rank = $index + 1;
      ?>
        <div class="rank-card rank-<?= $rank ?>" style="background: var(--bg-surface); border-radius: var(--radius-card); padding: 24px; text-align: center; box-shadow: var(--shadow-card); position: relative; overflow: hidden;">
          <span class="rank-number" style="display: inline-block; font-size: 1.25rem; font-weight: 800; margin-bottom: 12px; padding: 4px 16px; border-radius: 20px; background: rgba(0,0,0,0.05);">
            <?= $rank_badges[$rank] ?? "#{$rank}" ?>
          </span>
          <div style="width: 120px; height: 120px; margin: 0 auto 16px; border-radius: 50%; overflow: hidden; border: 4px solid var(--brand-color, #4f46e5);">
            <img src="<?= h($pet['image_path']) ?>" alt="<?= h($pet['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <h3 style="margin: 0 0 6px; font-size: 1.3rem; font-weight: 700;"><?= h($pet['name']) ?></h3>
          <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0 0 12px;"><?= h($pet['breed']) ?></p>
          <div class="likes" style="font-weight: 700; color: #e11d48; font-size: 1.1rem;">
            ❤️ <?= (int)$pet['likes_count'] ?> Likes
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Ranks 4+ Table List -->
  <?php if (!empty($other_ranks)): ?>
    <div style="background: var(--bg-surface); border-radius: var(--radius-card); box-shadow: var(--shadow-card); padding: 24px; overflow-x: auto;">
      <h3 style="margin: 0 0 20px; font-size: 1.2rem; font-weight: 700;">อันดับอื่นๆ ในชุมชน</h3>
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
        <thead>
          <tr style="border-bottom: 2px solid var(--bg-page); color: var(--text-muted);">
            <th style="padding: 12px; width: 80px; text-align: center;">Rank</th>
            <th style="padding: 12px;">Pet</th>
            <th style="padding: 12px;">Breed</th>
            <th style="padding: 12px; text-align: right;">Likes</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($other_ranks as $index => $pet): ?>
            <tr style="border-bottom: 1px solid var(--bg-page);">
              <td style="padding: 12px; text-align: center; font-weight: 700; color: var(--text-muted);">
                #<?= $index + 4 ?>
              </td>
              <td style="padding: 12px; display: flex; align-items: center; gap: 12px;">
                <img src="<?= h($pet['image_path']) ?>" alt="<?= h($pet['name']) ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                <span style="font-weight: 600;"><?= h($pet['name']) ?></span>
              </td>
              <td style="padding: 12px; color: var(--text-muted);"><?= h($pet['breed']) ?></td>
              <td style="padding: 12px; text-align: right; font-weight: 700; color: #e11d48;">
                ❤️ <?= (int)$pet['likes_count'] ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</main>