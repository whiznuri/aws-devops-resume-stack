<?php
/**
 * admin/users.php — User Management Controller
 */
declare(strict_types=1);
session_start();

// Guard Check: อนุญาตเฉพาะ Admin เท่านั้น
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /views/login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$message = '';
$error   = '';

// --- ACTION: UPDATE ROLE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    $target_id = (int)($_POST['user_id'] ?? 0);
    $new_role  = trim($_POST['new_role'] ?? '');

    if ($target_id === (int)($_SESSION['member_id'] ?? 0)) {
        $error = "ไม่สามารถเปลี่ยนสิทธิ์ของตนเองขณะใช้งานอยู่ได้";
    } elseif (in_array($new_role, ['member', 'staff', 'admin'], true) && $pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
            $stmt->execute([':role' => $new_role, ':id' => $target_id]);
            $message = "อัปเดตสิทธิ์ผู้ใช้ ID #{$target_id} เป็น '{$new_role}' เรียบร้อยแล้ว";
        } catch (PDOException $e) {
            $error = "เกิดข้อผิดพลาดในการอัปเดต: " . $e->getMessage();
        }
    } else {
        $error = "สิทธิ์ที่เลือกไม่ถูกต้องตามระบบ";
    }
}

// --- ACTION: DELETE USER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $target_id = (int)($_POST['user_id'] ?? 0);

    if ($target_id === (int)($_SESSION['member_id'] ?? 0)) {
        $error = "ไม่สามารถลบบัญชีของตนเองขณะใช้งานอยู่ได้";
    } elseif ($target_id > 0 && $pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $target_id]);
            $message = "ลบผู้ใช้ ID #{$target_id} เรียบร้อยแล้ว";
        } catch (PDOException $e) {
            $error = "ไม่สามารถลบผู้ใช้ได้: " . $e->getMessage();
        }
    }
}

// --- FETCH DATA ---
$search = trim($_GET['search'] ?? '');
$users  = [];

if ($pdo) {
    try {
        if ($search !== '') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE :s OR fullname LIKE :s OR email LIKE :s ORDER BY id DESC");
            $stmt->execute([':s' => "%{$search}%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
        }
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "ไม่สามารถดึงข้อมูลได้: " . $e->getMessage();
    }
}

// --- RENDER VIEW ---
$page_title = "User Management — DiffLovelyPet Admin";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/views/users_content.php';
require_once __DIR__ . '/includes/footer.php';