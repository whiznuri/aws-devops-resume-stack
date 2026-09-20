<?php
$page_title = 'Anuphan Natee — Hardware to Cloud Reliability Engineer';

// 1. ดึงส่วน <head> และ tag <body> เริ่มต้น
include_once __DIR__ . '/includes/header.php';

// 2. ดึง Navbar เมนูด้านบน
include_once __DIR__ . '/includes/navbar.php';

// 3. ดึงเนื้อหาหลัก (Hero, Timeline, Skills 4D, Projects)
include_once __DIR__ . '/pages/home.php';

// 4. ดึง Footer ปิดท้าย </footer></body></html>
include_once __DIR__ . '/includes/footer.php';
?>