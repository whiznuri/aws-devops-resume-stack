<?php
// ต่อฐานข้อมูล — อ่านค่าจาก environment variable ก่อน ถ้าไม่มีค่อยใช้ค่าในเครื่อง (dev)
//   dev  (docker compose ในเครื่อง): host "db",       user root, รหัสว่าง
//   prod (EC2):                      host "database", user ministock_app, รหัสจาก .env
// รหัสผ่านจริงไม่อยู่ในโค้ด → push ขึ้น GitHub ได้ปลอดภัย
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'ministock';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    ];
    $condb = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, $options);
} catch (PDOException $e) {
    error_log('DB connection failed: ' . $e->getMessage());   // เก็บรายละเอียดลง log (ดูด้วย docker logs)
    http_response_code(503);                                   // 503 = บริการใช้ไม่ได้ชั่วคราว (ไม่ใช่ 200)
    die("ระบบขัดข้องชั่วคราว กรุณาลองใหม่");                   // ผู้ใช้เห็นแค่ข้อความทั่วไป
}
