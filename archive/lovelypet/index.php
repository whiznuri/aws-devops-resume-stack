<?php

/**
 * index.php — Main Controller / Entry Point (Fixed Column Names All Cases)
 * Location: /index.php (project root)
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

// Helper function
if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

/* --------------------------------------------------------------------
 * Route Handling
 * ------------------------------------------------------------------ */
$page = $_GET['page'] ?? 'home';

/* --------------------------------------------------------------------
 * Execute Logic Based on Current Page Route
 * ------------------------------------------------------------------ */
switch ($page) {

    // --- ROUTE: LOGIN ---
    case 'login':
        $page_title = "Login — DiffLovelyPet";
        $view_file  = __DIR__ . '/views/login.php';
        break;

    // --- ROUTE: REGISTER ---
    case 'register':
        $page_title = "Register — DiffLovelyPet";
        $view_file  = __DIR__ . '/views/register.php';
        break;

    // --- ROUTE: ENCYCLOPEDIA PAGE ---
    case 'encyclopedia':
        $category = trim($_GET['cat'] ?? 'all');
        $search   = trim($_GET['q'] ?? '');
        $breeds   = [];

        if ($pdo) {
            try {
                // หากมีการพิมพ์ค้นหา ให้ใช้แบบสไตล์โปรเจกต์เก่า
                if ($search !== '') {
                    // เดิมใช้ :q ซ้ำ 2 จุดแต่ bind ค่าครั้งเดียว — db.php ปิด
                    // PDO::ATTR_EMULATE_PREPARES ไว้ (native prepare) ซึ่ง MySQL
                    // นับ :q สองจุดเป็นคนละ token กัน พอ bind ให้แค่ 1 ค่า จะ throw
                    // SQLSTATE[HY093] ทันที แล้วโดน catch ไปที่ exit("Database Error...")
                    // ด้านล่าง — หน้าเว็บเลยไม่เห็นการ์ดอะไรเลยทุกครั้งที่พิมพ์ค้นหา
                    // แก้โดยแยกชื่อ placeholder ให้ไม่ซ้ำกัน แล้ว bind ค่าเดียวกันทั้งคู่
                    $sql = "SELECT * FROM breeds WHERE name LIKE :q_name OR summary LIKE :q_summary ORDER BY id DESC";
                    $stmt = $pdo->prepare($sql);
                    $likeTerm = '%' . $search . '%';
                    $stmt->bindValue(':q_name', $likeTerm, PDO::PARAM_STR);
                    $stmt->bindValue(':q_summary', $likeTerm, PDO::PARAM_STR);
                    $stmt->execute();
                } else {
                    // กรณีไม่ได้ค้นหา ให้ดึงตามหมวดหมู่ปกติ
                    $sql = "SELECT * FROM breeds WHERE 1=1";
                    if (in_array($category, ['dog', 'cat', 'other'], true)) {
                        $sql .= " AND category = " . $pdo->quote($category);
                    }
                    $sql .= " ORDER BY id DESC";
                    $stmt = $pdo->query($sql);
                }

                $breeds = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // ปลดล็อกบรรทัดนี้เพื่อดู Error จริงถ้าคิวรีพัง
                exit("Database Error: " . $e->getMessage());
            }
        }

        $page_title = "Breed Encyclopedia — DiffLovelyPet";
        // เดิมชี้ไปที่ breed_encyclopedia_content.php แต่ไฟล์จริงที่มีคือ
        // breed_encyclopedia.php (ไม่มี "_content") — file_exists() เลย false
        // เสมอ ตกไปเข้า else-branch ที่ echo "ไม่พบไฟล์ View" แทน ซึ่งหมายความว่า
        // โซนสารานุกรมทั้งหน้าไม่เคยเรนเดอร์การ์ดเลยสักครั้ง ไม่ใช่แค่ตอนค้นหา
        $view_file  = __DIR__ . '/views/breed_encyclopedia.php';
        break;

    // --- ROUTE: LEADERBOARD PAGE ---
    case 'leaderboard':
        $leaderboard = [];

        if ($pdo) {
            try {
                $stmt = $pdo->query('
                    SELECT 
                        p.id AS pet_id, 
                        p.name AS name, 
                        b.name AS breed, 
                        p.like_count AS likes_count, 
                        p.image_path AS image_path 
                    FROM pets p
                    LEFT JOIN breeds b ON p.breed_id = b.id
                    ORDER BY p.like_count DESC 
                    LIMIT 10
                ');
                $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
            }
        }

        $page_title = "Pet Leaderboard — DiffLovelyPet";
        $view_file  = __DIR__ . '/views/leaderboard.php';
        break;

    // --- ROUTE: PET CATALOG (ทุกตัว พร้อม Filter สถานะ + Pagination) ---
    case 'catalog':
        $filter_status = trim($_GET['status'] ?? 'all');
        $page_num      = max(1, (int)($_GET['p'] ?? 1));
        $per_page      = 8;
        $offset        = ($page_num - 1) * $per_page;

        $pets        = [];
        $total_pets  = 0;
        $total_pages = 1;

        if ($pdo) {
            try {
                $where  = '';
                $params = [];
                if (in_array($filter_status, ['available', 'reserved', 'sold'], true)) {
                    $where = ' WHERE p.status = :status';
                    $params[':status'] = $filter_status;
                }

                // นับจำนวนทั้งหมดก่อน เพื่อคำนวณจำนวนหน้าสำหรับ Pagination
                $countStmt = $pdo->prepare('SELECT COUNT(*) FROM pets p' . $where);
                $countStmt->execute($params);
                $total_pets  = (int)$countStmt->fetchColumn();
                $total_pages = max(1, (int)ceil($total_pets / $per_page));

                // $per_page / $offset เป็น int ที่เราคุมเองอยู่แล้ว (ไม่ใช่ค่าดิบจาก user)
                // เลย interpolate ตรงๆ ได้ปลอดภัย ไม่ต้อง bind — เลี่ยงปัญหา
                // LIMIT/OFFSET แบบ named-placeholder ที่บางไดรเวอร์งอแงเรื่อง type
                $sql = "
                    SELECT
                        p.id AS pet_id,
                        p.name AS name,
                        COALESCE(b.name, p.custom_breed, 'ไม่ระบุสายพันธุ์') AS breed,
                        p.gender AS gender,
                        p.age_years AS age_years,
                        p.price AS price,
                        p.status AS status,
                        p.like_count AS likes_count,
                        p.image_path AS image_path
                    FROM pets p
                    LEFT JOIN breeds b ON p.breed_id = b.id
                    {$where}
                    ORDER BY p.created_at DESC
                    LIMIT {$per_page} OFFSET {$offset}
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
            }
        }

        $page_title = "Pet Catalog — DiffLovelyPet";
        $view_file  = __DIR__ . '/views/pet_catalog.php';
        break;

    // --- ROUTE: PET DETAIL (สัตว์เลี้ยงรายตัว) ---
    case 'pet_detail':
        $pet_id = (int)($_GET['id'] ?? 0);
        $pet    = null;

        if ($pdo && $pet_id > 0) {
            try {
                $stmt = $pdo->prepare('
                    SELECT
                        p.id AS pet_id,
                        p.name AS name,
                        COALESCE(b.name, p.custom_breed, \'ไม่ระบุสายพันธุ์\') AS breed,
                        p.gender AS gender,
                        p.age_years AS age_years,
                        p.price AS price,
                        p.status AS status,
                        p.like_count AS likes_count,
                        p.image_path AS image_path,
                        u.fullname AS owner_name
                    FROM pets p
                    LEFT JOIN breeds b ON p.breed_id = b.id
                    LEFT JOIN users u ON p.user_id = u.id
                    WHERE p.id = :id
                    LIMIT 1
                ');
                $stmt->execute([':id' => $pet_id]);
                $pet = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (PDOException $e) {
            }
        }

        $page_title = $pet ? h($pet['name']) . ' — DiffLovelyPet' : 'ไม่พบสัตว์เลี้ยง — DiffLovelyPet';
        $view_file  = __DIR__ . '/views/pet_detail.php';
        break;

    // --- ROUTE: CREATE PET PASSPORT (ต้องล็อกอิน + จัดการ Upload รูป) ---
    case 'create_pet':
        // ต้อง redirect "ก่อน" include header.php เสมอ เพราะ header.php
        // print HTML ออกไปแล้ว — ถ้าค่อย header('Location: ...') ทีหลัง
        // (เช่นในตัว view เอง) จะเจอ "headers already sent" แล้ว redirect ไม่ทำงาน
        if (!isset($_SESSION['member_name'])) {
            header('Location: /index.php?page=login');
            exit;
        }

        $create_pet_errors  = [];
        $all_breeds         = [];

        if ($pdo) {
            try {
                $stmt = $pdo->query('SELECT id, name, category FROM breeds ORDER BY category ASC, name ASC');
                $all_breeds = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name         = trim($_POST['name'] ?? '');
            $breed_id_in  = trim($_POST['breed_id'] ?? '');
            $custom_breed = trim($_POST['custom_breed'] ?? '');
            $gender       = $_POST['gender'] ?? 'male';
            $age_years    = (int)($_POST['age_years'] ?? 0);
            $price        = (float)($_POST['price'] ?? 0);
            $status       = $_POST['status'] ?? 'available';

            if ($name === '') {
                $create_pet_errors[] = 'กรุณากรอกชื่อสัตว์เลี้ยง';
            }
            if (!in_array($gender, ['male', 'female'], true)) {
                $create_pet_errors[] = 'เพศไม่ถูกต้อง';
            }
            if (!in_array($status, ['available', 'reserved', 'sold'], true)) {
                $create_pet_errors[] = 'สถานะไม่ถูกต้อง';
            }
            if ($age_years < 0 || $age_years > 50) {
                $create_pet_errors[] = 'อายุไม่ถูกต้อง';
            }
            if ($price < 0) {
                $create_pet_errors[] = 'ราคาต้องไม่ติดลบ';
            }

            // breed_id ต้องเป็น NULL ได้ (พันธุ์ผสม/ไม่ระบุ) — เช็กว่าถ้าเลือกมา
            // ต้องมีอยู่จริงในตาราง breeds เท่านั้น กัน id มั่วจาก client
            $breed_id = null;
            if ($breed_id_in !== '') {
                $breed_id_candidate = (int)$breed_id_in;
                if (in_array($breed_id_candidate, array_column($all_breeds, 'id'), true)) {
                    $breed_id = $breed_id_candidate;
                } else {
                    $create_pet_errors[] = 'สายพันธุ์ที่เลือกไม่ถูกต้อง';
                }
            }
            // เลือกจาก dropdown แล้วไม่ต้องเก็บ custom_breed คู่กัน, เลือกไม่ระบุค่อยเก็บ custom_breed
            $custom_breed = ($breed_id === null && $custom_breed !== '') ? $custom_breed : null;

            // --- Validate + ย้ายไฟล์รูปภาพ ---
            $image_path   = null;
            $allowed_mime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $max_size     = 2 * 1024 * 1024; // 2MB

            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    $create_pet_errors[] = 'อัปโหลดรูปภาพไม่สำเร็จ';
                } elseif ($_FILES['image']['size'] > $max_size) {
                    $create_pet_errors[] = 'ไฟล์รูปภาพต้องมีขนาดไม่เกิน 2MB';
                } else {
                    // เช็ก MIME type จริงจากเนื้อไฟล์ ไม่เชื่อ Content-Type ที่ browser ส่งมาเฉยๆ
                    $finfo     = finfo_open(FILEINFO_MIME_TYPE);
                    $real_mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
                    finfo_close($finfo);

                    if (!isset($allowed_mime[$real_mime])) {
                        $create_pet_errors[] = 'รองรับเฉพาะไฟล์ .jpg, .png, .webp เท่านั้น';
                    } else {
                        $upload_dir = __DIR__ . '/public/assets/uploads/pets/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        $new_filename = uniqid('pet_', true) . '.' . $allowed_mime[$real_mime];
                        $destination  = $upload_dir . $new_filename;

                        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                            $image_path = '/public/assets/uploads/pets/' . $new_filename;
                        } else {
                            $create_pet_errors[] = 'ไม่สามารถบันทึกไฟล์รูปภาพได้';
                        }
                    }
                }
            }

            // เช็กให้ชัดเจนก่อน insert — ถ้า session ไม่มี member_id (เช่น
            // login.php เวอร์ชันที่ใช้อยู่จริงยังไม่ได้ตั้งค่านี้ หรือตั้งคน
            // ละ key) แล้วปล่อยให้ (int)$_SESSION['member_id'] กลายเป็น 0 ไป
            // ดื้อๆ จะไปชน FOREIGN KEY (user_id) REFERENCES users(id) ตอน
            // insert แล้วได้ error ที่งงกว่าเดิมมาก ดักไว้ตรงนี้ให้ error
            // ชัดเจนกว่า
            if (empty($create_pet_errors) && !isset($_SESSION['member_id'])) {
                $create_pet_errors[] = 'ไม่พบ Session สมาชิก (member_id) กรุณาล็อกอินใหม่อีกครั้ง';
            }

            if (empty($create_pet_errors) && $pdo) {
                try {
                    $stmt = $pdo->prepare('
                        INSERT INTO pets (name, breed_id, custom_breed, user_id, gender, age_years, price, status, image_path, like_count)
                        VALUES (:name, :breed_id, :custom_breed, :user_id, :gender, :age_years, :price, :status, :image_path, 0)
                    ');
                    $stmt->execute([
                        ':name'         => $name,
                        ':breed_id'     => $breed_id,
                        ':custom_breed' => $custom_breed,
                        ':user_id'      => (int)$_SESSION['member_id'],
                        ':gender'       => $gender,
                        ':age_years'    => $age_years,
                        ':price'        => $price,
                        ':status'       => $status,
                        ':image_path'   => $image_path,
                    ]);

                    $new_pet_id = (int)$pdo->lastInsertId();
                    header('Location: /index.php?page=pet_detail&id=' . $new_pet_id);
                    exit;
                } catch (PDOException $e) {
                    // เดิมโชว์แค่ข้อความ generic เลยไม่รู้ว่าพังเพราะอะไร —
                    // ตอนนี้ต่อ error จริงจาก MySQL ให้เห็นเลย (เหมาะกับตอน
                    // dev/staging — พอขึ้น production ค่อยตัด $e->getMessage()
                    // ออก แล้วไป error_log() แทนเพื่อไม่ให้หลุดรายละเอียด DB)
                    $create_pet_errors[] = 'บันทึกข้อมูลไม่สำเร็จ: ' . $e->getMessage();
                }
            }
        }

        $page_title = "Create Pet Passport — DiffLovelyPet";
        $view_file  = __DIR__ . '/views/create_pet.php';
        break;

    // --- ACTION: LIKE A PET (++like_count) ---
    // ไม่ใช่หน้า view — รับ POST แล้ว redirect กลับที่เดิมเสมอ ไม่มี
    // $view_file เพราะ route นี้จบด้วย header()+exit ทุกทาง ไม่ไหลไปเข้า
    // ส่วน Assembly Views ด้านล่างเลย
    case 'like_pet':
        // ต้อง redirect ก่อน include header.php เสมอ (เหตุผลเดียวกับ create_pet)
        if (!isset($_SESSION['member_name'])) {
            header('Location: /index.php?page=login');
            exit;
        }

        $like_pet_id = (int)($_POST['pet_id'] ?? 0);
        $redirect_to = $_POST['redirect_to'] ?? '/index.php?page=catalog';

        // กัน Open Redirect — อนุญาตแค่ path ที่ขึ้นต้นด้วย /index.php เท่านั้น
        if (!is_string($redirect_to) || strpos($redirect_to, '/index.php') !== 0) {
            $redirect_to = '/index.php?page=catalog';
        }

        if ($pdo && $like_pet_id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // ยังไม่มีตาราง likes แยกเก็บว่าใครไลก์ตัวไหนไปแล้วบ้าง (เก็บ
            // แค่ like_count รวมในตาราง pets) เลยกันกดไลก์ซ้ำแบบง่ายๆ ด้วย
            // session ก่อน — เพียงพอกันสแปมกดรัวๆ ในเซสชันเดียว แต่ยังไม่ใช่
            // ระบบกันไลก์ซ้ำถาวรข้ามอุปกรณ์/เบราว์เซอร์ (ต้องมีตาราง likes
            // แยกถ้าอยากได้แบบนั้นจริงๆ — บอกได้ถ้าอยากให้ทำต่อ)
            $_SESSION['liked_pets'] = $_SESSION['liked_pets'] ?? [];
            if (!in_array($like_pet_id, $_SESSION['liked_pets'], true)) {
                try {
                    $stmt = $pdo->prepare('UPDATE pets SET like_count = like_count + 1 WHERE id = :id');
                    $stmt->execute([':id' => $like_pet_id]);
                    $_SESSION['liked_pets'][] = $like_pet_id;
                } catch (PDOException $e) {
                }
            }
        }

        header('Location: ' . $redirect_to);
        exit;

    // --- ROUTE: HOME PAGE (Default) ---
    case 'home':
    default:
        $breeds      = [];
        $pets        = [];
        $leaderboard = [];

        if ($pdo) {
            try {
                // 1. ดึงสายพันธุ์แนะนำ
                $stmt = $pdo->query('SELECT id, name, category, summary, image_path FROM breeds ORDER BY id DESC LIMIT 6');
                $breeds = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 2. ดึงแคตตาล็อกสัตว์เลี้ยง (แก้ไข p.name & p.image_path)
                $stmt = $pdo->query('
                    SELECT 
                        p.id AS pet_id, 
                        p.name AS name, 
                        b.name AS breed, 
                        p.like_count AS likes_count, 
                        p.image_path AS image_path 
                    FROM pets p
                    LEFT JOIN breeds b ON p.breed_id = b.id
                    ORDER BY p.created_at DESC 
                    LIMIT 8
                ');
                $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 3. ดึง Top 3 Leaderboard (แก้ไข p.name & p.image_path)
                $stmt = $pdo->query('
                    SELECT 
                        p.id AS pet_id, 
                        p.name AS name, 
                        b.name AS breed, 
                        p.like_count AS likes_count, 
                        p.image_path AS image_path 
                    FROM pets p
                    LEFT JOIN breeds b ON p.breed_id = b.id
                    ORDER BY p.like_count DESC 
                    LIMIT 3
                ');
                $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
            }
        }

        $page_title = "DiffLovelyPet — Pet Encyclopedia & Digital Pet Passport Community";
        $view_file  = __DIR__ . '/views/home_content.php';
        break;
}

/* --------------------------------------------------------------------
 * Assembly Views (ประกอบร่างหน้าเว็บ)
 * ------------------------------------------------------------------ */
require_once __DIR__ . '/includes/header.php';

if (file_exists($view_file)) {
    require_once $view_file;
} else {
    echo "<div class='container' style='padding: 40px;'>⚠️ ไม่พบไฟล์ View: " . h($view_file) . "</div>";
}

require_once __DIR__ . '/includes/footer.php';
