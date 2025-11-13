# Git Repository Sozlash va Push Qilish

## Qadamlar

### 1. Git Repository'ni Boshlash (Agar hali qilinmagan bo'lsa)

```bash
git init
```

### 2. Barcha Fayllarni Qo'shish

```bash
git add .
```

### 3. Birinchi Commit

```bash
git commit -m "Initial commit: Anonim so'rovnoma platformasi"
```

### 4. Remote Repository'ni Qo'shish

```bash
git remote add origin git@github.com:shahzodkenjayev/anonim.xoshimov.uz.git
```

### 5. Branch Nomini O'zgartirish (Agar kerak bo'lsa)

```bash
git branch -M main
```

### 6. Push Qilish

```bash
git push -u origin main
```

## To'liq Buyruqlar Ketma-ketligi

```bash
# 1. Git'ni ishga tushirish
git init

# 2. Barcha fayllarni qo'shish
git add .

# 3. Commit qilish
git commit -m "Initial commit: Anonim so'rovnoma platformasi"

# 4. Remote repository'ni qo'shish
git remote add origin git@github.com:shahzodkenjayev/anonim.xoshimov.uz.git

# 5. Branch nomini main qilish
git branch -M main

# 6. Push qilish
git push -u origin main
```

## Agar Remote Repository Allaqachon Qo'shilgan Bo'lsa

Agar `git remote add` xatolik bersa (remote allaqachon mavjud), quyidagilarni bajaring:

```bash
# Remote'ni o'chirish
git remote remove origin

# Yoki yangilash
git remote set-url origin git@github.com:shahzodkenjayev/anonim.xoshimov.uz.git
```

## SSH Key Sozlash (Agar Kerak Bo'lsa)

Agar SSH key sozlanmagan bo'lsa:

```bash
# SSH key yaratish
ssh-keygen -t ed25519 -C "your_email@example.com"

# SSH key'ni ko'rish
cat ~/.ssh/id_ed25519.pub

# GitHub'ga qo'shish: Settings > SSH and GPG keys > New SSH key
```

Yoki HTTPS ishlatish:

```bash
git remote add origin https://github.com/shahzodkenjayev/anonim.xoshimov.uz.git
```

## Keyingi Yangilanishlar

Keyinchalik o'zgarishlarni push qilish:

```bash
git add .
git commit -m "Yangilanishlar tavsifi"
git push
```

## Tekshirish

```bash
# Remote repository'ni ko'rish
git remote -v

# Status'ni ko'rish
git status

# Branch'larni ko'rish
git branch
```
