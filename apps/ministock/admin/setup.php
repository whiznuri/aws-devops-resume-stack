<?php
// ===========================================================
// setup.php — สร้างแอดมินคนแรก (รันครั้งเดียว แล้วลบไฟล์นี้ทิ้ง)
// เปิด localhost:8090/admin/setup.php
// ===========================================================
require_once '../config/condb.php';

$count = $condb->query("SELECT COUNT(*) FROM admins")->fetchColumn();

if ($count > 0) {
    die('มีแอดมินอยู่แล้ว ลบไฟล์ setup.php ทิ้งได้เลย');
}

$username = 'admin';
$password = 'admin1234';   // รหัสสำหรับทดสอบในเครื่องเท่านั้น

// password_hash: ใช้ bcrypt + สุ่ม salt ให้อัตโนมัติ
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $condb->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
$stmt->execute([$username, $hash]);

echo "สร้างแอดมินแล้ว: $username<br>";
echo "hash ที่เก็บในฐานข้อมูล: <code>" . htmlspecialchars($hash) . "</code><br>";
echo '<a href="login.php">ไปหน้า login</a>';
