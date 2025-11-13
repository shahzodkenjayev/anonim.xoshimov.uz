# Cache Tozalash Qo'llanmasi

## Server Cache Tozalash Usullari

### 1. PHP OPcache Tozalash

#### Terminal orqali:

```bash
# SSH orqali serverga kirib
php -r "opcache_reset();"
```

#### PHP fayl orqali:

```php
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache tozalandi";
}
?>
```

#### `clear_cache.php` faylini ishlatish:

1. Browser'da oching: `https://anonim.xoshimov.uz/clear_cache.php`
2. Yoki terminaldan: `php clear_cache.php`

### 2. Apache Cache Tozalash

#### Apache'ni qayta ishga tushirish:

```bash
sudo systemctl restart apache2
# yoki
sudo service apache2 restart
```

#### .htaccess orqali:

`.htaccess` faylida cache control sozlamalari mavjud. Agar o'zgartirishlar ko'rinmasa, Apache'ni qayta ishga tushiring.

### 3. Browser Cache Tozalash

#### Chrome/Firefox:

- `Ctrl + Shift + Delete` (Windows/Linux)
- `Cmd + Shift + Delete` (Mac)
- "Cached images and files" ni tanlang va "Clear data" ni bosing

#### Hard Refresh:

- `Ctrl + F5` (Windows/Linux)
- `Cmd + Shift + R` (Mac)

### 4. Telegram Cache Tozalash

Telegram linklarni cache qiladi. Yangi meta teglarni ko'rish uchun:

#### Usul 1: Query parameter qo'shish

```
https://anonim.xoshimov.uz/?v=1
https://anonim.xoshimov.uz/?t=1234567890
```

#### Usul 2: Telegram Bot API orqali

1. [@WebpageBot](https://t.me/WebpageBot) ga link yuboring
2. Bot yangi versiyani ko'rsatadi

#### Usul 3: Kutilish

- Telegram cache odatda 24-48 soat ichida yangilanadi
- Yoki server admin bilan bog'lanib, cache'ni tozalash so'rang

### 5. CDN Cache (agar mavjud bo'lsa)

Agar Cloudflare yoki boshqa CDN ishlatilsa:

#### Cloudflare:

1. Cloudflare Dashboard'ga kiring
2. Caching > Purge Everything
3. Yoki faqat bitta URL ni tozalash

#### Boshqa CDN:

- Har bir CDN'ning o'z cache tozalash usuli bor
- CDN provider bilan bog'laning

### 6. Meta Teglarni Tekshirish

#### Browser orqali:

1. Sahifani oching: `https://anonim.xoshimov.uz/`
2. `Ctrl + U` (View Source)
3. `<head>` qismida meta teglarni qidiring:
   ```html
   <meta property="og:title" content="..." />
   <meta property="og:description" content="..." />
   <meta property="og:image" content="..." />
   ```

#### Online tool'lar:

- [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
- [Twitter Card Validator](https://cards-dev.twitter.com/validator)
- [LinkedIn Post Inspector](https://www.linkedin.com/post-inspector/)

### 7. Server Loglarni Tekshirish

```bash
# Apache error log
tail -f /var/log/apache2/error.log

# PHP error log
tail -f /var/log/php_errors.log

# Yoki project log
tail -f logs/security.log
```

## Tez Yechimlar

### Meta teglar ko'rinmayapti:

1. ✅ Browser cache'ni tozalash (Ctrl+Shift+Delete)
2. ✅ Hard refresh (Ctrl+F5)
3. ✅ `clear_cache.php` ni ishga tushirish
4. ✅ Apache'ni qayta ishga tushirish
5. ✅ Telegram'da linkga `?v=1` qo'shish

### Telegram'da eski versiya ko'rinmoqda:

1. ✅ Linkga timestamp qo'shing: `?t=<?php echo time(); ?>`
2. ✅ Kuting (24-48 soat)
3. ✅ [@WebpageBot](https://t.me/WebpageBot) dan foydalaning

### Server cache tozalanmayapti:

1. ✅ Server admin bilan bog'laning
2. ✅ PHP OPcache sozlamalarini tekshiring
3. ✅ Apache mod_headers yoqilganligini tekshiring

## Xavfsizlik

⚠️ **Muhim:** `clear_cache.php` faylini production'da:

- O'chirish yoki
- IP whitelist qo'shish yoki
- Parol bilan himoyalash

tavsiya etiladi.
