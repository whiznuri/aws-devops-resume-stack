<?php
// ===========================================================
// api/products.php — REST API ของสินค้า (อ่านอย่างเดียว)
//
//   GET /api/products.php        → รายการสินค้าที่เปิดขาย
//   GET /api/products.php?id=1   → สินค้าชิ้นเดียว
//
// ตอบเป็น JSON เสมอ + ใช้ HTTP status code บอกผล:
//   200 สำเร็จ / 400 ส่งค่ามาผิด / 404 ไม่พบ / 405 method ไม่รองรับ / 500 ฝั่งเซิร์ฟเวอร์พัง
//
// Stateless: ไม่ใช้ session — ทุก request ต้องมีข้อมูลครบในตัวเอง
// ===========================================================
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/condb.php';

// ส่งคำตอบ แล้วจบการทำงานทันที
function respond(int $status, array $body): void
{
    http_response_code($status);
    // JSON_UNESCAPED_UNICODE: ภาษาไทยออกมาเป็นตัวอ่านได้ ไม่ใช่ ล...
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// แปลงชนิดข้อมูลให้ถูก (PDO คืนทุกอย่างจาก MySQL มาเป็น string)
function toProduct(array $row): array
{
    return [
        'id'       => (int)$row['id'],
        'name'     => $row['name'],
        'price'    => (float)$row['price'],
        'stock'    => (int)$row['stock'],
        'in_stock' => (int)$row['stock'] > 0,
    ];
}

// API นี้อ่านอย่างเดียว: method อื่นตอบ 405 + บอกว่ารับ method อะไรได้บ้าง
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    respond(405, ['error' => 'Method Not Allowed']);
}

try {
    // ----- สินค้าชิ้นเดียว -----
    if (isset($_GET['id'])) {
        // validate: ต้องเป็นจำนวนเต็มบวก ไม่งั้นตอบ 400 (ความผิดอยู่ที่ฝั่งคนเรียก)
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            respond(400, ['error' => 'id ต้องเป็นจำนวนเต็มบวก']);
        }

        $stmt = $condb->prepare("SELECT id, name, price, stock FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        // ไม่มี หรือถูกระงับขาย → 404 เหมือนกัน (ไม่บอกคนนอกว่ามีสินค้าที่ซ่อนอยู่)
        if (!$row) {
            respond(404, ['error' => 'ไม่พบสินค้า']);
        }
        respond(200, ['data' => toProduct($row)]);
    }

    // ----- รายการทั้งหมด -----
    $rows = $condb->query("SELECT id, name, price, stock FROM products WHERE is_active = 1 ORDER BY id")->fetchAll();
    respond(200, [
        'data'  => array_map('toProduct', $rows),
        'count' => count($rows),
    ]);

} catch (PDOException $e) {
    error_log('API error: ' . $e->getMessage());       // รายละเอียดเก็บใน log
    respond(500, ['error' => 'Internal Server Error']); // คนเรียกเห็นแค่ข้อความทั่วไป
}
