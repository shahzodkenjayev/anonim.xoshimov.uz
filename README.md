# Anonim So'rovnoma Platformasi

PHP va MySQL yordamida yaratilgan anonim so'rovnoma platformasi. Talabalar o'qituvchilar, dekanlar va koordinatorlar haqida anonim so'rovnoma to'ldirishlari mumkin.

## Xususiyatlar

- ✅ Anonim so'rovnoma tizimi
- ✅ Xodimlar haqida baholash (1-5 yulduzcha)
- ✅ Matnli javoblar
- ✅ Admin paneli - xodimlar va natijalarni boshqarish
- ✅ Har bir talaba har bir xodimga faqat bir marta javob bera oladi
- ✅ Responsive dizayn

## O'rnatish

### Local Development (XAMPP)

#### 1. Database yaratish

XAMPP yoki boshqa MySQL server ishga tushirilgan bo'lishi kerak.

1. `database.sql` faylini MySQL'ga import qiling
2. Yoki phpMyAdmin orqali SQL faylni ishga tushiring

#### 2. .env Fayl Sozlash

`.env.example` faylini nusxalab `.env` fayl yarating:

```bash
# Windows
copy .env.example .env

# Linux/Mac
cp .env.example .env
```

`.env` faylni ochib, database ma'lumotlarini kiriting:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=anonim_survey
```

**Batafsil:** `ENV_SETUP.md` fayliga qarang.

#### 3. Web server

XAMPP yoki boshqa web server ishga tushirilgan bo'lishi kerak.

Loyiha `htdocs/anonim` papkasida bo'lishi kerak.

### Production Server (Serverga O'tkazish)

#### Tezkor Boshlash

1. **Git Repository yaratish:**

   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin https://github.com/YOUR_USERNAME/anonim-survey.git
   git push -u origin main
   ```

2. **Serverga yuklash:**

   ```bash
   # Serverga SSH orqali kiring
   ssh root@your-server.com

   # Web root papkasiga o'ting
   cd /var/www/html

   # Repository'ni clone qiling
   git clone https://github.com/YOUR_USERNAME/anonim-survey.git anonim
   cd anonim
   ```

3. **Database yaratish:**

   ```bash
   mysql -u root -p
   CREATE DATABASE anonim_survey CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   mysql -u root -p anonim_survey < database.sql
   ```

4. **.env Fayl Sozlash:**

   ```bash
   cp .env.example .env
   nano .env
   ```

   ```env
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=sizning_parol
   DB_NAME=anonim_survey
   APP_ENV=production
   APP_DEBUG=false
   ```

   **Xavfsizlik:**

   ```bash
   chmod 600 .env
   ```

5. **Ruxsatlarni sozlash:**
   ```bash
   chown -R www-data:www-data /var/www/html/anonim
   chmod -R 755 /var/www/html/anonim
   ```

**Batafsil qo'llanma:** `QUICK_START.md` va `DEPLOYMENT.md` fayllariga qarang.

## Default foydalanuvchi

- **Username:** admin
- **Password:** admin123

## Foydalanish

### Talabalar uchun

1. Tizimga kirish (admin yoki talaba hisobi bilan)
2. Xodimlar ro'yxatidan birini tanlash
3. So'rovnoma to'ldirish
4. Topshirish

### Admin uchun

1. Admin hisobi bilan kirish
2. Dashboard - umumiy statistika
3. Xodimlar - xodimlarni qo'shish/o'chirish
4. Savollar - so'rovnoma savollarini boshqarish
5. Natijalar - barcha xodimlar natijalarini ko'rish
6. Har bir xodim uchun batafsil natijalar

## Struktura

```
anonim/
├── admin/
│   ├── dashboard.php          # Admin bosh sahifa
│   ├── employees.php          # Xodimlarni boshqarish
│   ├── employee_results.php   # Xodim natijalari
│   ├── questions.php          # Savollarni boshqarish
│   └── results.php            # Barcha natijalar
├── student/
│   └── survey.php             # Talabalar so'rovnoma sahifasi
├── assets/
│   └── css/
│       └── style.css          # CSS stillar
├── config/
│   └── database.php           # Database konfiguratsiyasi
├── includes/
│   └── functions.php          # Umumiy funksiyalar
├── index.php                  # Login sahifa
├── logout.php                 # Chiqish
├── database.sql               # Database schema
└── README.md                  # Ushbu fayl
```

## Database jadvallari

- **users** - Foydalanuvchilar (admin va talabalar)
- **employees** - Xodimlar (o'qituvchilar, dekanlar, koordinatorlar)
- **questions** - So'rovnoma savollari
- **survey_responses** - So'rovnoma javoblari
- **survey_submissions** - Topshirilgan so'rovnomalar (bir marta javob berishni ta'minlash uchun)

## Xavfsizlik

- Parollar bcrypt yordamida hash qilinadi
- SQL injectiondan himoya (prepared statements)
- XSSdan himoya (htmlspecialchars)
- Session-based authentication

## Texnologiyalar

- PHP 7.4+
- MySQL 5.7+
- HTML5, CSS3
- Vanilla JavaScript (kerak bo'lganda)

## Muallif

Anonim so'rovnoma platformasi - 2024
