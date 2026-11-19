SET NAMES utf8mb4;   -- ให้ตอน import อ่านภาษาไทยถูก (กัน double encoding)

-- ===========================================================
-- Mini Shopping — schema
-- customers (ลูกค้า) / products (สินค้า) / orders (หัวออเดอร์)
-- order_items (รายการในออเดอร์) / admins (ผู้ดูแลระบบ)
-- ===========================================================
CREATE DATABASE IF NOT EXISTS ministock DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ministock;

-- ลูกค้า (สมัครสมาชิกเอง)
CREATE TABLE customers (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(100) NOT NULL,
  email          VARCHAR(100) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- สินค้า
CREATE TABLE products (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  price       DECIMAL(10,2) NOT NULL,            -- ราคาต่อหน่วย
  stock       INT NOT NULL DEFAULT 0,            -- จำนวนคงเหลือ
  is_active   TINYINT(1) NOT NULL DEFAULT 1,     -- 1 = เปิดขาย / 0 = ระงับการขาย (soft delete)
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- หัวออเดอร์ (1 แถว = กดสั่งซื้อจากตะกร้า 1 ครั้ง)
CREATE TABLE orders (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  customer_id  INT NOT NULL,
  total        DECIMAL(10,2) NOT NULL DEFAULT 0,  -- ยอดรวมของออเดอร์นี้
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id)
) ENGINE=InnoDB;

-- รายการในออเดอร์ (1 ออเดอร์มีได้หลายแถว = หลายสินค้าในตะกร้า)
CREATE TABLE order_items (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  order_id    INT NOT NULL,
  product_id  INT NOT NULL,
  qty         INT NOT NULL,
  price       DECIMAL(10,2) NOT NULL,            -- ราคา ณ ตอนสั่ง (กันราคาเปลี่ยนทีหลัง)
  FOREIGN KEY (order_id)   REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- ผู้ดูแลระบบ (หลังบ้าน)
CREATE TABLE admins (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(50)  NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,          -- เก็บผลของ password_hash() ไม่เก็บรหัสจริง
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ข้อมูลตัวอย่าง
INSERT INTO products (name, price, stock) VALUES
('ลิปสติก',  200.00, 10),
('ครีมบำรุง', 350.00,  5),
('แป้งฝุ่น',  150.00,  0);   -- ตัวนี้สต็อก 0 ไว้ทดสอบว่าสั่งไม่ได้
