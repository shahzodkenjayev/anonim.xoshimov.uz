# .env Fayl Sozlash Qo'llanmasi

## .env Fayl Nima?

`.env` fayl - bu loyiha sozlamalarini saqlash uchun ishlatiladigan fayl. Bu fayl Git'ga yuklanmaydi (xavfsizlik uchun) va har bir muhit (local, server) uchun alohida sozlanadi.

## Qanday Ishlatish?

### 1. .env Fayl Yaratish

Loyiha ildizida `.env.example` fayl mavjud. Uni nusxalab `.env` fayl yarating:

**Windows:**

```cmd
copy .env.example .env
```

**Linux/Mac:**

```bash
cp .env.example .env
```

### 2. .env Faylni Sozlash

`.env` faylni ochib, o'z ma'lumotlaringizni kiriting:

```env
# Database Konfiguratsiyasi
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASS=sizning_parolingiz
DB_NAME=anonim_survey
DB_CHARSET=utf8mb4

# Application Settings
APP_ENV=development
APP_DEBUG=true

# Session Settings
SESSION_LIFETIME=7200
```

### 3. Local Development (XAMPP)

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=anonim_survey
```

### 4. Production Server

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=kuchli_server_paroli
DB_NAME=anonim_survey
APP_ENV=production
APP_DEBUG=false
```

### 5. Remote Database (Agar kerak bo'lsa)

```env
DB_HOST=206.189.114.116
DB_PORT=3306
DB_USER=root
DB_PASS=Amina2021.206
DB_NAME=anonim_survey
```

## Xavfsizlik

⚠️ **Muhim:**

- `.env` fayl Git'ga yuklanmaydi (`.gitignore` da)
- `.env` faylda parollar va maxfiy ma'lumotlar saqlanadi
- Har bir muhit uchun alohida `.env` fayl yarating
- `.env` fayl ruxsatlarini cheklang: `chmod 600 .env`

## Fayl Struktura

```
anonim/
├── .env                 # Shaxsiy sozlamalar (Git'ga yuklanmaydi)
├── .env.example         # Namuna fayl (Git'ga yuklanadi)
├── config/
│   ├── env_loader.php   # .env faylni yuklash funksiyasi
│   └── database.php     # Database konfiguratsiyasi (.env dan o'qiydi)
└── ...
```

## Muammolarni Hal Qilish

### .env Fayl Topilmayapti

Agar `.env` fayl mavjud bo'lmasa, `.env.example` avtomatik nusxalanadi.

### O'zgarishlar Ishlamayapti

1. `.env` fayl to'g'ri joyda ekanligini tekshiring (loyiha ildizida)
2. Fayl formatini tekshiring (key=value, bo'sh qatorlar, kommentlar # bilan)
3. Web server'ni qayta ishga tushiring

### Xatoliklar

Agar database ulanishida muammo bo'lsa:

1. `.env` faylda ma'lumotlar to'g'riligini tekshiring
2. `config/database.php` faylida default qiymatlar ishlatilayotganini tekshiring
3. PHP error log'ni ko'ring

## Foydali Buyruqlar

```bash
# .env fayl mavjudligini tekshirish
ls -la .env

# .env fayl ruxsatlarini sozlash
chmod 600 .env

# .env fayl mazmunini ko'rish (parollarni ko'rsatmaydi)
grep -v "PASS" .env
```

## Qo'shimcha Ma'lumot

- `.env.example` - Namuna fayl, barcha kerakli o'zgaruvchilar bilan
- `config/env_loader.php` - .env faylni yuklash funksiyasi
- `config/database.php` - Database konfiguratsiyasi .env dan o'qiydi
