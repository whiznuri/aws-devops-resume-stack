<?php
// ออกจากระบบฝั่งลูกค้า (ไม่ยุ่งกับ session ของแอดมิน)
session_start();
unset($_SESSION['customer_id'], $_SESSION['customer_name']);
session_regenerate_id(true);
header('Location: index.php');
exit;
