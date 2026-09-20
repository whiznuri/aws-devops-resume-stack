<?php
/**
 * admin/dashboard.php — Central Back-Office Controller
 */
declare(strict_types=1);
session_start();

// 1. Centralized Guard Check: อนุญาตเฉพาะ Admin และ Staff[cite: 11]
$user_role = $_SESSION['role'] ?? 'member';
if (!in_array($user_role, ['admin', 'staff'], true)) {
    header('Location: /views/login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

// 2. Routing Parameter Target
$page    = $_GET['page'] ?? 'overview';
$search  = trim($_GET['search'] ?? '');
$message = '';
$error   = '';

// 3. Page Controller & Action Processing
switch ($page) {

    // --- PAGE: USER MANAGEMENT (Admin Only) ---
    case 'users':
        if ($user_role !== 'admin') {
            header('Location: /admin/dashboard.php?page=overview');
            exit;
        }

        // Action: Update Role[cite: 15]
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_role') {
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

        // Action: Delete User[cite: 15]
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_user') {
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

        // Fetch Users Data
        $users = [];
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
                $error = "ไม่สามารถดึงข้อมูลผู้ใช้ได้: " . $e->getMessage();
            }
        }

        $page_title = "จัดการผู้ใช้ & สิทธิ์ — DiffLovelyPet Admin";
        $view_file  = __DIR__ . '/views/users_content.php';
        break;


    // --- PAGE: BREED MANAGEMENT ---
    case 'breeds':
        // Action: Add / Edit Breed[cite: 14]
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_breed') {
            $breed_id   = (int)($_POST['breed_id'] ?? 0);
            $name       = trim($_POST['name'] ?? '');
            $category   = trim($_POST['category'] ?? '');
            $summary    = trim($_POST['summary'] ?? '');
            $image_path = trim($_POST['image_path'] ?? '');

            if (empty($name) || !in_array($category, ['dog', 'cat', 'other'], true)) {
                $error = "กรุณากรอกชื่อสายพันธุ์และเลือกหมวดหมู่ให้ถูกต้อง";
            } elseif ($pdo) {
                try {
                    if ($breed_id > 0) {
                        $stmt = $pdo->prepare("UPDATE breeds SET name = :name, category = :category, summary = :summary, image_path = :image_path WHERE id = :id");
                        $stmt->execute([
                            ':name'       => $name,
                            ':category'   => $category,
                            ':summary'    => $summary,
                            ':image_path' => $image_path,
                            ':id'         => $breed_id
                        ]);
                        $message = "อัปเดตข้อมูลสายพันธุ์ '{$name}' เรียบร้อยแล้ว";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO breeds (name, category, summary, image_path) VALUES (:name, :category, :summary, :image_path)");
                        $stmt->execute([
                            ':name'       => $name,
                            ':category'   => $category,
                            ':summary'    => $summary,
                            ':image_path' => $image_path
                        ]);
                        $message = "เพิ่มสายพันธุ์ '{$name}' เรียบร้อยแล้ว";
                    }
                } catch (PDOException $e) {
                    $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
                }
            }
        }

        // Action: Delete Breed[cite: 14]
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_breed') {
            $target_id = (int)($_POST['breed_id'] ?? 0);

            if ($target_id > 0 && $pdo) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM breeds WHERE id = :id");
                    $stmt->execute([':id' => $target_id]);
                    $message = "ลบสายพันธุ์ ID #{$target_id} เรียบร้อยแล้ว";
                } catch (PDOException $e) {
                    $error = "ไม่สามารถลบข้อมูลได้: " . $e->getMessage();
                }
            }
        }

        // Fetch Breeds Data
        $breeds = [];
        if ($pdo) {
            try {
                if ($search !== '') {
                    $stmt = $pdo->prepare("SELECT * FROM breeds WHERE name LIKE :s OR category LIKE :s OR summary LIKE :s ORDER BY id DESC");
                    $stmt->execute([':s' => "%{$search}%"]);
                } else {
                    $stmt = $pdo->query("SELECT * FROM breeds ORDER BY id DESC");
                }
                $breeds = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $error = "ไม่สามารถดึงข้อมูลสายพันธุ์ได้: " . $e->getMessage();
            }
        }

        $page_title = "จัดการสายพันธุ์ — DiffLovelyPet Admin";
        $view_file  = __DIR__ . '/views/breeds_content.php';
        break;


        // --- PAGE: PET MANAGEMENT ---
    case 'pets':
        // Action: Add / Edit Pet
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_pet') {
            $pet_id     = (int)($_POST['pet_id'] ?? 0);
            $name       = trim($_POST['name'] ?? '');
            $breed_id   = (int)($_POST['breed_id'] ?? 0);
            $age_years  = (int)($_POST['age_years'] ?? 0);
            $gender     = trim($_POST['gender'] ?? 'male');
            $status     = trim($_POST['status'] ?? 'available');
            $price      = (float)($_POST['price'] ?? 0);
            $image_path = trim($_POST['image_path'] ?? '');

            if (empty($name) || $breed_id <= 0) {
                $error = "กรุณากรอกชื่อสัตว์เลี้ยงและเลือกสายพันธุ์ให้ถูกต้อง";
            } elseif ($pdo) {
                try {
                    if ($pet_id > 0) {
                        $stmt = $pdo->prepare("UPDATE pets SET name = :name, breed_id = :breed_id, age_years = :age_years, gender = :gender, status = :status, price = :price, image_path = :image_path WHERE id = :id");
                        $stmt->execute([
                            ':name'       => $name,
                            ':breed_id'   => $breed_id,
                            ':age_years'  => $age_years,
                            ':gender'     => $gender,
                            ':status'     => $status,
                            ':price'      => $price,
                            ':image_path' => $image_path,
                            ':id'         => $pet_id
                        ]);
                        $message = "อัปเดตข้อมูล '{$name}' เรียบร้อยแล้ว";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO pets (name, breed_id, age_years, gender, status, price, image_path) VALUES (:name, :breed_id, :age_years, :gender, :status, :price, :image_path)");
                        $stmt->execute([
                            ':name'       => $name,
                            ':breed_id'   => $breed_id,
                            ':age_years'  => $age_years,
                            ':gender'     => $gender,
                            ':status'     => $status,
                            ':price'      => $price,
                            ':image_path' => $image_path
                        ]);
                        $message = "เพิ่มสัตว์เลี้ยง '{$name}' เรียบร้อยแล้ว";
                    }
                } catch (PDOException $e) {
                    $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
                }
            }
        }

        // Action: Delete Pet
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_pet') {
            $target_id = (int)($_POST['pet_id'] ?? 0);

            if ($target_id > 0 && $pdo) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM pets WHERE id = :id");
                    $stmt->execute([':id' => $target_id]);
                    $message = "ลบข้อมูลสัตว์เลี้ยง ID #{$target_id} เรียบร้อยแล้ว";
                } catch (PDOException $e) {
                    $error = "ไม่สามารถลบข้อมูลได้: " . $e->getMessage();
                }
            }
        }

        // Fetch Pets Data (JOIN breeds เพื่อดึงชื่อสายพันธุ์)
        $pets   = [];
        $breeds = [];
        if ($pdo) {
            try {
                // ดึงรายชื่อ Breeds ไว้ใส่ใน Dropdown ของ Modal
                $breeds = $pdo->query("SELECT id, name FROM breeds ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

                if ($search !== '') {
                    $stmt = $pdo->prepare("SELECT p.*, b.name AS breed_name FROM pets p LEFT JOIN breeds b ON p.breed_id = b.id WHERE p.name LIKE :s OR b.name LIKE :s ORDER BY p.id DESC");
                    $stmt->execute([':s' => "%{$search}%"]);
                } else {
                    $stmt = $pdo->query("SELECT p.*, b.name AS breed_name FROM pets p LEFT JOIN breeds b ON p.breed_id = b.id ORDER BY p.id DESC");
                }
                $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $error = "ไม่สามารถดึงข้อมูลสัตว์เลี้ยงได้: " . $e->getMessage();
            }
        }

        $page_title = "จัดการสัตว์เลี้ยง — DiffLovelyPet Admin";
        $view_file  = __DIR__ . '/views/pets_content.php';
        break;

    // --- PAGE: PET TYPE MANAGEMENT (ใหม่ — เดิมไม่มีเลย) ---
    case 'pet_types':
        // Action: Add / Edit Pet Type
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_pet_type') {
            $type_id   = (int)($_POST['type_id'] ?? 0);
            $type_name = trim($_POST['type_name'] ?? '');

            if ($type_name === '') {
                $error = "กรุณากรอกชื่อประเภทสัตว์เลี้ยง";
            } elseif ($pdo) {
                try {
                    if ($type_id > 0) {
                        $stmt = $pdo->prepare("UPDATE pet_types SET type_name = :name WHERE id = :id");
                        $stmt->execute([':name' => $type_name, ':id' => $type_id]);
                        $message = "อัปเดตประเภทสัตว์เลี้ยง '{$type_name}' เรียบร้อยแล้ว";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO pet_types (type_name) VALUES (:name)");
                        $stmt->execute([':name' => $type_name]);
                        $message = "เพิ่มประเภทสัตว์เลี้ยง '{$type_name}' เรียบร้อยแล้ว";
                    }
                } catch (PDOException $e) {
                    $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
                }
            }
        }

        // Action: Delete Pet Type
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_pet_type') {
            $target_id = (int)($_POST['type_id'] ?? 0);

            if ($target_id > 0 && $pdo) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM pet_types WHERE id = :id");
                    $stmt->execute([':id' => $target_id]);
                    $message = "ลบประเภทสัตว์เลี้ยง ID #{$target_id} เรียบร้อยแล้ว";
                } catch (PDOException $e) {
                    // pet_types อาจมี pets อ้างอิงอยู่ผ่าน FK ในบางสคีมา —
                    // ถ้าลบไม่ได้เพราะติด constraint จะเห็น error ตรงนี้ชัดเจน
                    $error = "ไม่สามารถลบข้อมูลได้: " . $e->getMessage();
                }
            }
        }

        // Fetch Pet Types Data
        $pet_types = [];
        if ($pdo) {
            try {
                if ($search !== '') {
                    $stmt = $pdo->prepare("SELECT * FROM pet_types WHERE type_name LIKE :s ORDER BY id DESC");
                    $stmt->execute([':s' => "%{$search}%"]);
                } else {
                    $stmt = $pdo->query("SELECT * FROM pet_types ORDER BY id DESC");
                }
                $pet_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $error = "ไม่สามารถดึงข้อมูลประเภทสัตว์เลี้ยงได้: " . $e->getMessage();
            }
        }

        $page_title = "จัดการประเภทสัตว์เลี้ยง — DiffLovelyPet Admin";
        $view_file  = __DIR__ . '/views/pet_types_content.php';
        break;

    // --- PAGE: OVERVIEW DASHBOARD (Default) ---
    default:
        $total_users     = 0;
        $total_pets      = 0;
        $total_breeds    = 0;
        $total_pet_types = 0;

        if ($pdo) {
            try {
                $total_users     = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                $total_pets      = (int)$pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();
                $total_breeds    = (int)$pdo->query("SELECT COUNT(*) FROM breeds")->fetchColumn();
                $total_pet_types = (int)$pdo->query("SELECT COUNT(*) FROM pet_types")->fetchColumn();
            } catch (PDOException $e) {
                $error = "ไม่สามารถดึงข้อมูลสถิติได้: " . $e->getMessage();
            }
        }

        $page_title = "Dashboard Overview — DiffLovelyPet Admin";
        $view_file  = __DIR__ . '/views/dashboard_content.php';
        break;
}

// 4. Render Layout Framework & Specific View Content[cite: 11]
require_once __DIR__ . '/includes/header.php';

if (file_exists($view_file)) {
    require_once $view_file;
} else {
    echo "<div style='padding: 24px; color: var(--pastel-pink-text);'>⚠️ ไม่พบไฟล์ View: " . h($view_file) . "</div>";
}

require_once __DIR__ . '/includes/footer.php';
