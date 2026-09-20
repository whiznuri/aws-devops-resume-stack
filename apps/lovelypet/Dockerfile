# 1. ดึง Image พื้นฐาน PHP 8.2 ที่มี Apache ติดมาด้วย
FROM php:8.2-apache

# 2. ติดตั้ง Extension pdo_mysql เพื่อให้ PHP คุยกับ MySQL ได้
RUN docker-php-ext-install pdo pdo_mysql mysqli

# 3. เปิดใช้งาน mod_rewrite ของ Apache
RUN a2enmod rewrite

# 4. คัดลอกไฟล์โค้ดเว็บทั้งหมด เข้าไปไว้ใน Container
COPY . /var/www/html/

# 5. กำหนดสิทธิ์โฟลเดอร์ให้ Apache
RUN chown -R www-data:www-data /var/www/html