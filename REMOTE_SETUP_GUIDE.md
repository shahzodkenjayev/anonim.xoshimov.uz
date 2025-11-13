# Remote MySQL Server Sozlash Qo'llanmasi

## Muammo

Remote MySQL serverga ulanish rad etilmoqda: "Подключение не установлено, т.к. конечный компьютер отверг запрос на подключение"

## Yechimlar

### 1. MySQL Server Remote Access Sozlash

Remote serverga SSH orqali kiring va quyidagi qadamlarni bajaring:

#### A) MySQL Config Faylini Tahrirlash

```bash
# Ubuntu/Debian
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Yoki
sudo nano /etc/mysql/my.cnf

# CentOS/RHEL
sudo nano /etc/my.cnf
```

Quyidagi qatorni toping:

```
bind-address = 127.0.0.1
```

Quyidagiga o'zgartiring:

```
bind-address = 0.0.0.0
```

Yoki butunlay olib tashlang:

```
# bind-address = 127.0.0.1
```

#### B) MySQL'ni Qayta Ishga Tushirish

```bash
sudo systemctl restart mysql
# yoki
    sudo systemctl restart mysqld
```

### 2. MySQL Foydalanuvchisi Remote Access Huquqi

MySQL'ga kirib, quyidagi buyruqlarni bajaring:

```bash
mysql -u root -p
```

Keyin MySQL'da:

```sql
-- Ma'lum database uchun
GRANT ALL PRIVILEGES ON anonim_survey.* TO 'root'@'%' IDENTIFIED BY 'Amina2021.206' WITH GRANT OPTION;

-- Yoki barcha database'lar uchun
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'Amina2021.206' WITH GRANT OPTION;

-- Huquqlarni yangilash
FLUSH PRIVILEGES;

-- Tekshirish
SELECT user, host FROM mysql.user WHERE user='root';
```

Agar MySQL 8.0+ versiyasida bo'lsa:

```sql
-- Avval foydalanuvchini yarating
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY 'Amina2021.206';

-- Huquqlarni bering
GRANT ALL PRIVILEGES ON anonim_survey.* TO 'root'@'%';

-- Yoki barcha database'lar uchun
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%';

FLUSH PRIVILEGES;
```

### 3. Firewall Sozlash

#### Ubuntu/Debian (UFW):

```bash
# 3306 portini ochish
sudo ufw allow 3306/tcp

# Yoki ma'lum IP uchun
sudo ufw allow from YOUR_LOCAL_IP to any port 3306

# Firewall holatini tekshirish
sudo ufw status
```

#### CentOS/RHEL (firewalld):

```bash
# 3306 portini ochish
sudo firewall-cmd --add-port=3306/tcp --permanent
sudo firewall-cmd --reload

# Tekshirish
sudo firewall-cmd --list-ports
```

#### Iptables (agar ishlatilsa):

```bash
sudo iptables -A INPUT -p tcp --dport 3306 -j ACCEPT
sudo iptables-save
```

### 4. Database Yaratish

Agar database mavjud bo'lmasa:

```sql
CREATE DATABASE IF NOT EXISTS anonim_survey
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

### 5. Ulanishni Test Qilish

Remote serverdan:

```bash
mysql -h 206.189.114.116 -u root -p
```

Yoki local mashinadan:

```bash
mysql -h 206.189.114.116 -P 3306 -u root -p
```

## Alternativ Yechimlar

### Variant 1: SSH Tunnel (Agar to'g'ridan-to'g'ri ulanish ishlamasa)

Local mashinada SSH tunnel yarating:

```bash
ssh -L 3307:localhost:3306 root@206.189.114.116
```

Keyin `config/database.php` faylida:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
```

### Variant 2: cPanel yoki boshqa Panel

Agar serverda cPanel, Plesk yoki boshqa panel bo'lsa:

1. Panel'ga kiring
2. "Remote MySQL" yoki "MySQL Remote Access" bo'limiga o'ting
3. Sizning IP manzilingizni qo'shing
4. Database va foydalanuvchini yarating

### Variant 3: Database Hosting Xizmati

Agar shared hosting ishlatilsa, hosting provayderdan:

- Remote MySQL access huquqini so'rang
- Yoki faqat localhost'dan ulanishga ruxsat bering (va SSH tunnel ishlating)

## Tekshirish

Barcha o'zgarishlardan keyin, `test_connection.php` faylini qayta oching va tekshiring.

## Xavfsizlik Eslatmalari

⚠️ **Muhim**: Remote MySQL access xavfsizlik xavfi tug'diradi!

1. Kuchli parol ishlating
2. Faqat kerakli IP manzillarga ruxsat bering
3. Firewall qoidalarini to'g'ri sozlang
4. Muntazam yangilanishlarni o'tkazing

## Yordam

Agar muammo hal bo'lmasa:

1. Server administratoriga murojaat qiling
2. Hosting provayder bilan bog'laning
3. Server loglarini tekshiring: `/var/log/mysql/error.log`
