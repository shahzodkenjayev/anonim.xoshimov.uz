# Serverga O'tkazish Qo'llanmasi

## 1. Git Repository Yaratish

### GitHub'da Repository Yaratish

1. GitHub'ga kiring va yangi repository yarating
2. Repository nomi: `anonim-survey` yoki boshqa nom

### Local Repository Yaratish

```bash
# Git'ni ishga tushirish
git init

# Barcha fayllarni qo'shish
git add .

# Birinchi commit
git commit -m "Initial commit: Anonim so'rovnoma platformasi"

# Remote repository'ni qo'shish
git remote add origin https://github.com/YOUR_USERNAME/anonim-survey.git

# Push qilish
git branch -M main
git push -u origin main
```

## 2. Serverga O'tkazish

### SSH orqali Serverga Kirish

```bash
ssh root@206.189.114.116
# yoki
ssh username@your-server.com
```

### Serverda Git Clone Qilish

```bash
# Web root papkasiga o'tish (masalan, /var/www/html yoki /home/username/public_html)
cd /var/www/html
# yoki
cd /home/username/public_html

# Repository'ni clone qilish
git clone https://github.com/YOUR_USERNAME/anonim-survey.git anonim

# Papkaga kirish
cd anonim
```

### Yoki FTP/SFTP orqali

1. Barcha fayllarni ZIP qilib oling
2. Serverga yuklang va oching
3. `anonim` papkasiga joylashtiring

## 3. Database Yaratish

### Serverda MySQL'ga Kirish

```bash
mysql -u root -p
```

### Database va Foydalanuvchi Yaratish

```sql
-- Database yaratish
CREATE DATABASE anonim_survey CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Foydalanuvchi yaratish (agar kerak bo'lsa)
CREATE USER 'anonim_user'@'localhost' IDENTIFIED BY 'kuchli_parol_bu_yerda';
GRANT ALL PRIVILEGES ON anonim_survey.* TO 'anonim_user'@'localhost';
FLUSH PRIVILEGES;

-- Database'ni import qilish
USE anonim_survey;
SOURCE /var/www/html/anonim/database.sql;
-- yoki
mysql -u root -p anonim_survey < /var/www/html/anonim/database.sql
```

## 4. Database Konfiguratsiyasini Sozlash

### config/database.php Faylini Tahrirlash

Serverda:

```bash
nano /var/www/html/anonim/config/database.php
```

Quyidagiga o'zgartiring:

```php
<?php
// Database konfiguratsiyasi (Server uchun)
define('DB_HOST', 'localhost');  // Serverda localhost ishlatiladi
define('DB_PORT', '3306');
define('DB_USER', 'root');  // yoki 'anonim_user'
define('DB_PASS', 'sizning_parolingiz');  // Server MySQL paroli
define('DB_NAME', 'anonim_survey');
define('DB_CHARSET', 'utf8mb4');
define('DB_TIMEOUT', 10);

// ... qolgan kod o'zgarmaydi
```

## 5. File Permissions (Ruxsatlar)

```bash
# Web server fayllariga yozish huquqi
chown -R www-data:www-data /var/www/html/anonim
# yoki
chown -R apache:apache /var/www/html/anonim

# Papka ruxsatlari
chmod -R 755 /var/www/html/anonim
chmod -R 775 /var/www/html/anonim/assets  # agar kerak bo'lsa
```

## 6. Apache/Nginx Virtual Host Sozlash

### Apache Virtual Host

```bash
sudo nano /etc/apache2/sites-available/anonim.conf
```

Quyidagi konfiguratsiyani qo'shing:

```apache
<VirtualHost *:80>
    ServerName anonim.yourdomain.com
    # yoki
    # ServerName yourdomain.com/anonim

    DocumentRoot /var/www/html/anonim

    <Directory /var/www/html/anonim>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/anonim_error.log
    CustomLog ${APACHE_LOG_DIR}/anonim_access.log combined
</VirtualHost>
```

Virtual host'ni faollashtirish:

```bash
sudo a2ensite anonim.conf
sudo systemctl reload apache2
```

### Nginx Virtual Host

```bash
sudo nano /etc/nginx/sites-available/anonim
```

```nginx
server {
    listen 80;
    server_name anonim.yourdomain.com;

    root /var/www/html/anonim;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Faollashtirish:

```bash
sudo ln -s /etc/nginx/sites-available/anonim /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 7. SSL Sertifikat (HTTPS) - Ixtiyoriy

```bash
# Let's Encrypt
sudo certbot --apache -d anonim.yourdomain.com
# yoki
sudo certbot --nginx -d anonim.yourdomain.com
```

## 8. Test Qilish

1. Browser'da oching: `http://anonim.yourdomain.com` yoki `http://yourdomain.com/anonim`
2. Login sahifasini tekshiring
3. Admin paneliga kirib, database ulanishini tekshiring

## 9. Yangilanishlar

### Git orqali Yangilash

```bash
cd /var/www/html/anonim
git pull origin main
```

### Yoki Qo'lda

1. Yangi fayllarni serverga yuklang
2. Eski fayllarni yangilang

## 10. Xavfsizlik

### .htaccess Fayli (Apache uchun)

`/var/www/html/anonim/.htaccess` yarating:

```apache
# Xavfsizlik
<Files "config/database.php">
    Order allow,deny
    Deny from all
</Files>

# PHP xatoliklarni yashirish (production)
php_flag display_errors Off
php_flag log_errors On

# Directory listing'ni o'chirish
Options -Indexes
```

### Test Fayllarni O'chirish

```bash
rm /var/www/html/anonim/test_connection.php
rm /var/www/html/anonim/REMOTE_SETUP_GUIDE.md  # agar kerak bo'lmasa
```

## 11. Backup

### Database Backup

```bash
# Backup yaratish
mysqldump -u root -p anonim_survey > backup_$(date +%Y%m%d).sql

# Restore qilish
mysql -u root -p anonim_survey < backup_20240101.sql
```

### Fayllar Backup

```bash
tar -czf anonim_backup_$(date +%Y%m%d).tar.gz /var/www/html/anonim
```

## Muammolarni Hal Qilish

### Database Ulanish Xatosi

1. `config/database.php` faylini tekshiring
2. MySQL ishlayotganini tekshiring: `sudo systemctl status mysql`
3. Database mavjudligini tekshiring: `mysql -u root -p -e "SHOW DATABASES;"`

### Permission Xatolari

```bash
chmod -R 755 /var/www/html/anonim
chown -R www-data:www-data /var/www/html/anonim
```

### PHP Xatoliklari

```bash
# PHP error log'ni tekshiring
tail -f /var/log/php/error.log
# yoki
tail -f /var/log/apache2/error.log
```

## Foydali Buyruqlar

```bash
# Apache qayta ishga tushirish
sudo systemctl restart apache2

# MySQL qayta ishga tushirish
sudo systemctl restart mysql

# Git status
cd /var/www/html/anonim && git status

# Loglarni ko'rish
tail -f /var/log/apache2/anonim_error.log
```
