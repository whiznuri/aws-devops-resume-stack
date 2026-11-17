</main> <!-- ปิด tag main -->

<footer class="site-footer">
    <div class="footer-container">
        <p>&copy; <?= date('Y') ?> Anuphan "Diff" Natee. All rights reserved.</p>
    </div>
</footer>

<script>
// ระบบ Smooth Scroll ซ่อน Hash URL
document.querySelectorAll('a[data-target]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const targetId = this.getAttribute('data-target');
    const targetElement = document.getElementById(targetId);
    if (targetElement) {
      targetElement.scrollIntoView({ behavior: 'smooth' });
    }
  });
});

// ระบบ Expandable Timeline Accordion
function toggleExpand(elementId) {
  const item = document.getElementById(elementId);
  const isOpen = item.classList.contains('active');
  
  // ปิดตัวอื่นทั้งหมด (ถ้าอยากให้เปิดได้ทีละตัว)
  document.querySelectorAll('.timeline-item').forEach(el => {
    el.classList.remove('active');
    const icon = el.querySelector('.expand-icon');
    if (icon) icon.textContent = '+';
  });

  // สลับสถานะตัวที่กด
  if (!isOpen) {
    item.classList.add('active');
    item.querySelector('.expand-icon').textContent = '−';
  }
}
</script>
</body>
</html>