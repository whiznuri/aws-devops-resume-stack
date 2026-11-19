<?php
// include ไฟล์นี้ไว้บนสุดของทุกหน้าหลังบ้าน
// ยังไม่ล็อกอิน → เด้งไปหน้า login
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
