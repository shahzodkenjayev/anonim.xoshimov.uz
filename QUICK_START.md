# Tezkor Boshlash Qo'llanmasi

## Serverga O'tkazish - Qisqa Qo'llanma

### 1. Git Repository Yaratish (Agar Git ishlatmoqchi bo'lsangiz)

```bash
# Local mashinada
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/YOUR_USERNAME/anonim-survey.git
git push -u origin main
```

### 2. Serverga Yuklash

#### Variant A: Git Clone (Tavsiya etiladi)

```bash
# Serverga SSH orqali kiring
ssh root@206.189.114.116

# Web root papkasiga o'ting
cd /var/www/html  # yoki /home/username/public_html

# Repository'ni clone qiling
git clone https://github.com/YOUR_USERNAME/anonim-survey.git anonim
cd anonim
```

#### Variant B: FTP/SFTP orqali

1. Barcha fayllarni ZIP qiling
2. Serverga yuklang va oching
3. `anonim` papkasiga joylashtiring

### 3. Database Yaratish va Sozlash

```bash
# MySQL'ga kiring
mysql -u root -p

# Database yaratish
CREATE DATABASE anonim_survey CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Database'ni import qilish
USE anonim_survey;
SOURCE /var/www/html/anonim/database.sql;
# yoki
exit
mysql -u root -p anonim_survey < /var/www/html/anonim/database.sql
```

### 4. .env Fayl Sozlash

```bash
# .env.example dan .env yaratish
cp /var/www/html/anonim/.env.example /var/www/html/anonim/.env

# .env faylni tahrirlang
nano /var/www/html/anonim/.env
```

Quyidagiga o'zgartiring:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=sizning_parol
DB_NAME=anonim_survey
APP_ENV=production
APP_DEBUG=false
```

**Xavfsizlik:** `.env` fayl ruxsatlarini cheklang:

```bash
chmod 600 /var/www/html/anonim/.env
```

### 5. Ruxsatlarni Sozlash

```bash
chown -R www-data:www-data /var/www/html/anonim
chmod -R 755 /var/www/html/anonim
```

### 6. Apache Virtual Host (Agar kerak bo'lsa)

```bash
sudo nano /etc/apache2/sites-available/anonim.conf
```

```apache
<VirtualHost *:80>
    ServerName anonim.yourdomain.com
    DocumentRoot /var/www/html/anonim

    <Directory /var/www/html/anonim>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```bash
sudo a2ensite anonim.conf
sudo systemctl reload apache2
```

### 7. Test Qilish

Browser'da oching:

- `http://206.189.114.116/anonim`
- yoki `http://yourdomain.com/anonim`
- yoki `http://anonim.yourdomain.com` (agar subdomain sozlangan bo'lsa)

### 8. Login

- **Admin:** `admin` / `admin123`

## Muammolarni Hal Qilish

### Database Ulanish Xatosi

1. MySQL ishlayotganini tekshiring:

   ```bash
   sudo systemctl status mysql
   ```

2. Database mavjudligini tekshiring:

   ```bash
   mysql -u root -p -e "SHOW DATABASES;"
   ```

3. `config/database.php` faylini tekshiring

### Permission Xatolari

```bash
chmod -R 755 /var/www/html/anonim
chown -R www-data:www-data /var/www/html/anonim
```

### 404 Xatosi

1. Apache mod_rewrite yoqilganini tekshiring:

   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. `.htaccess` fayli mavjudligini tekshiring

## Keyingi Qadamlar

1. **Xavfsizlik:**

   - `test_connection.php` faylini o'chiring
   - Kuchli parollardan foydalaning
   - SSL sertifikat o'rnating (Let's Encrypt)

2. **Backup:**

   - Database backup yarating
   - Fayllarni backup qiling

3. **Monitoring:**
   - Error loglarni kuzatib boring
   - Performance'ni tekshiring

## Foydali Linklar

- Batafsil qo'llanma: `DEPLOYMENT.md`
- Database sozlash: `REMOTE_SETUP_GUIDE.md`
