<?php

/**
 * includes/footer.php — Shared Footer & Scripts
 *
 * Location: /includes/footer.php
 */

declare(strict_types=1);
?>
<footer class="site-footer">
  <div class="container">
    <span>&copy; <?= date('Y') ?> LovelyPet. All rights reserved.</span>
    <div class="footer-links">
      <a href="https://diffan.dev/">Contact</a>
      <a href="/index.php?page=encyclopedia">Encyclopedia</a>
      <a href="/index.php?page=leaderboard">Leaderboard</a>
    </div>
  </div>
</footer>

<button class="back-to-top" id="backToTop" aria-label="Back to top">&#8593;</button>

<script>
  // Back-to-top button
  var backToTop = document.getElementById('backToTop');
  if (backToTop) {
    window.addEventListener('scroll', function() {
      if (window.scrollY > 300) {
        backToTop.classList.add('is-visible');
      } else {
        backToTop.classList.remove('is-visible');
      }
    });
    backToTop.addEventListener('click', function() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // Breed encyclopedia filter tabs
  var filterTabs = document.querySelectorAll('#breedFilterTabs .filter-tab');
  var breedCards = document.querySelectorAll('#breedGrid .breed-card');

  if (filterTabs.length > 0) {
    filterTabs.forEach(function(tab) {
      tab.addEventListener('click', function() {
        filterTabs.forEach(function(t) {
          t.classList.remove('is-active');
        });
        tab.classList.add('is-active');

        var filter = tab.getAttribute('data-filter');
        breedCards.forEach(function(card) {
          var match = filter === 'all' || card.getAttribute('data-category') === filter;
          card.classList.toggle('is-hidden', !match);
        });
      });
    });
  }
</script>

</body>

</html>