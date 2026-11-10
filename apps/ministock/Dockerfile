FROM php:8.2-apache
RUN docker-php-ext-install pdo_mysql mysqli

# ซ่อนเวอร์ชัน Apache/OS ใน header และหน้า error
RUN printf "ServerTokens Prod\nServerSignature Off\n" > /etc/apache2/conf-enabled/zz-hide-version.conf

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini

# prod: ก๊อปโค้ดเข้า image (dev ในเครื่องยังใช้ bind mount ทับโฟลเดอร์นี้เหมือนเดิม)
COPY . /var/www/html/
RUN rm -rf /var/www/html/docker   # php.ini ไม่ต้องอยู่ในโฟลเดอร์เว็บ
