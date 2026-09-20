<?php
/**
 * db.php — Database Connection Handler (PDO)
 * LovelyPet App — Multi-container Docker Environment
 *
 * Location: /includes/db.php
 */

declare(strict_types=1);

$db_host    = 'db';
$db_name    = 'lovelypet_db';
$db_user    = 'lovely_user';
$db_pass    = 'secret123';
$db_charset = 'utf8mb4';

$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";

$pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    // บังคับ UTF-8 ภาษาไทยผ่าน PDO Init Command ทุกครั้งที่เชื่อมต่อ
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $pdo_options);
} catch (PDOException $e) {
    // ซ่อนข้อความ Error ฝั่งเซิร์ฟเวอร์ไว้ใน Log เพื่อความปลอดภัย
    error_log('[LovelyPet] DB connection failed: ' . $e->getMessage());
    $pdo = null;
}
