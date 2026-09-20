# ใช้ Official PHP + Apache Image
FROM php:8.2-apache

# เปิดใช้งาน mod_rewrite ของ Apache (จำเป็นสำหรับไฟล์ .htaccess)
RUN a2enmod rewrite

# ก๊อปปี้ไฟล์เว็บทั้งหมดใน apps/web-resume ไปไว้ใน Web Root ของ Container
COPY . /var/www/html/

# ตั้งค่า Permission ให้ Apache อ่านและรันไฟล์ได้ถูกต้อง
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# เปิด Port 80 สำหรับรับ Web Traffic
EXPOSE 80