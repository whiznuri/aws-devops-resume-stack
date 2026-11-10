# Deploy Mini Shopping → https://diffan.dev/shop/ (ผ่าน CI/CD)

⏰ **หยุดที่ 10:00 ไม่ว่าเสร็จหรือไม่** → ถ้าไม่เสร็จ ทำ "ถอยกลับ" ท้ายไฟล์

```
push ครั้งเดียว ──▶ CI build 3 images ──▶ Docker Hub ──▶ EC2: git pull + compose up + nginx -t + reload
แล้ว SSH ทำงานครั้งเดียว: สร้าง DB / user / .env / แอดมิน
```

---

## A. ในเครื่อง — เตรียม repo แล้ว push

### A1. แตก zip ทับ repo
```powershell
Expand-Archive "$HOME\Downloads\repo-ministock.zip" -DestinationPath D:\Diff\Projects\aws-devops-resume-stack -Force
cd D:\Diff\Projects\aws-devops-resume-stack
```
ได้ `apps\ministock\` ใหม่ + `deploy.yml` กับ `nginx\conf.d\default.conf` ที่แก้แล้ว

### A2. เพิ่ม service ใน `docker-compose.yml` (แก้มือ วางใต้ `services:` ต่อจาก lovelypet)
```yaml
  ministock:
    image: whiznuri363924an/ministock-app:latest
    container_name: ministock
    environment:
      DB_HOST: database
      DB_NAME: ministock
      DB_USER: ministock_app
      DB_PASS: ${MINISTOCK_DB_PASS}   # มาจาก .env บน EC2 (ไม่อยู่ใน git)
    depends_on:
      - database
    mem_limit: 128m
    restart: always
```
- ไม่มี `ports:` → เข้าได้ทาง nginx `/shop/` เท่านั้น
- **ไม่ต้อง**เพิ่ม ministock ใน `depends_on` ของ nginx

### A3. ตรวจก่อน push
```powershell
git status
git diff -- .github nginx docker-compose.yml
```
ต้องเห็น: deploy.yml เพิ่ม ministock + `set -e` + `nginx -t` / default.conf เพิ่ม `location /shop/` / compose เพิ่ม service

### A4. push
```powershell
git add apps/ministock .github/workflows/deploy.yml nginx/conf.d/default.conf docker-compose.yml
git commit -m "Add ministock (Mini Shopping) at /shop/ with runtime DNS resolve in nginx"
git push origin main
```
ดู GitHub → Actions ให้เขียว ✅
(ตอนนี้ `/shop/` จะขึ้น "ระบบขัดข้องชั่วคราว" — ปกติ เพราะ DB ยังไม่มี)

---

## B. บน EC2 — งานครั้งเดียว (SSH)

```bash
cd ~/aws-devops-resume-stack
read -rsp "MySQL root password: " ROOTPW; echo
```

### B1. สร้าง DB + ตาราง (ไฟล์มากับ git pull แล้ว)
```bash
docker exec -i mysql-db mysql -uroot -p"$ROOTPW" < apps/ministock/database/schema.sql
```

### B2. user ของแอป — สิทธิ์แค่ SELECT / INSERT / UPDATE (ไม่มี DELETE)
```bash
APPPW=$(openssl rand -hex 16)
echo "MINISTOCK_DB_PASS=$APPPW" >> .env
chmod 600 .env
docker exec -i mysql-db mysql -uroot -p"$ROOTPW" -e \
  "CREATE USER 'ministock_app'@'%' IDENTIFIED BY '$APPPW';
   GRANT SELECT, INSERT, UPDATE ON ministock.* TO 'ministock_app'@'%';"
```

### B3. สร้าง container ใหม่ให้อ่าน .env (env ถูกอ่านตอนสร้าง container เท่านั้น)
```bash
docker compose up -d --force-recreate --no-deps ministock
```

### B4. สร้างแอดมิน (ห้ามใช้ admin1234)
```bash
read -rsp "รหัสแอดมินใหม่: " ADMINPW; echo
HASH=$(docker exec ministock php -r 'echo password_hash($argv[1], PASSWORD_DEFAULT);' -- "$ADMINPW")
docker exec -i mysql-db mysql -uroot -p"$ROOTPW" ministock -e \
  "INSERT INTO admins (username, password_hash) VALUES ('admin', '$HASH');"
unset ROOTPW APPPW ADMINPW HASH
```

### B5. เช็ก
```bash
curl -sI https://diffan.dev/shop/      | head -1   # 200
curl -sI https://diffan.dev/            | head -1   # 200  ← resume ต้องยังอยู่
curl -sI https://lovelypet.diffan.dev   | head -1   # 200
curl -sI https://diffan.dev/shop/ | grep -i x-powered-by   # ต้องว่าง
docker logs ministock --tail 20                     # ไม่มี error
```
เบราว์เซอร์: สมัคร → ตะกร้า → สั่งซื้อ → `https://diffan.dev/shop/admin/`

---

## ⏪ ถอยกลับ (แบบ GitOps — ใช้ git อย่างเดียว)
```powershell
git revert HEAD
git push origin main
```
CI จะ deploy เวอร์ชันก่อนหน้า → `--remove-orphans` ลบ container ministock ทิ้ง → nginx กลับเป็น config เดิม
DB `ministock` ค้างไว้ได้ ไม่กระทบ lovelypet
