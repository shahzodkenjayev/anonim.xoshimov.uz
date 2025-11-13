<?php
/**
 * Base path funksiyalari
 * CSS, JS va boshqa asset fayllar uchun to'g'ri path'lar
 */

// Base URL ni aniqlash
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                 (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
                 ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    
    // REQUEST_URI dan base path ni olish
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    
    // Root sahifada bo'lsa
    if ($scriptName === '/index.php' || $scriptName === '/') {
        return $protocol . $host;
    }
    
    $path = dirname($scriptName);
    
    // Agar admin papkasida bo'lsa, bir daraja yuqoriga chiqamiz
    if (strpos($path, '/admin') !== false) {
        $path = dirname($path);
    }
    
    // Agar student papkasida bo'lsa
    if (strpos($path, '/student') !== false) {
        $path = dirname($path);
    }
    
    // Agar path '/' bo'lsa yoki root bo'lsa, bo'sh qoldiramiz
    if ($path === '/' || $path === '\\' || $path === '.') {
        $path = '';
    }
    
    return $protocol . $host . $path;
}

// Admin papkasida ekanligini aniqlash
function isAdminPath() {
    $script = $_SERVER['SCRIPT_NAME'];
    return strpos($script, '/admin/') !== false || strpos($script, '\\admin\\') !== false;
}

// Asset fayl path'ini olish
function asset($path) {
    $baseUrl = getBaseUrl();
    // Path'dan boshidagi slash'ni olib tashlash
    $path = ltrim($path, '/');
    return $baseUrl . '/' . $path;
}

// CSS fayl path'ini olish
function css($file) {
    // Agar admin papkasida bo'lsa, admin/assets dan foydalanamiz
    if (isAdminPath()) {
        return asset('admin/assets/css/' . $file);
    }
    return asset('assets/css/' . $file);
}

// JS fayl path'ini olish
function js($file) {
    // Agar admin papkasida bo'lsa, admin/assets dan foydalanamiz
    if (isAdminPath()) {
        return asset('admin/assets/js/' . $file);
    }
    return asset('assets/js/' . $file);
}

// Image fayl path'ini olish
function image($file) {
    // Agar admin papkasida bo'lsa, admin/assets dan foydalanamiz
    if (isAdminPath()) {
        return asset('admin/assets/images/' . $file);
    }
    return asset('assets/images/' . $file);
}

// URL ga timestamp qo'shish (Telegram cache uchun)
function addCacheBuster($url, $forceNew = false) {
    // Agar forceNew true bo'lsa, eski timestamp ni yangilaymiz
    if ($forceNew) {
        // Eski v yoki t parametrni olib tashlash
        $url = preg_replace('/[?&](v|t)=\d+/', '', $url);
        // Agar ? bilan tugasa, olib tashlash
        $url = rtrim($url, '?&');
    }
    
    // Agar URL'da allaqachon v yoki t parametri bo'lsa va forceNew false bo'lsa, o'zgartirmaymiz
    if (!$forceNew && (strpos($url, '?v=') !== false || strpos($url, '&v=') !== false || 
        strpos($url, '?t=') !== false || strpos($url, '&t=') !== false)) {
        return $url;
    }
    
    // Timestamp qo'shish
    $separator = strpos($url, '?') !== false ? '&' : '?';
    return $url . $separator . 'v=' . time();
}

// Open Graph meta teglarini yaratish
function generateMetaTags($title, $description, $image = null, $type = 'website', $url = null, $addCacheBuster = true) {
    $baseUrl = getBaseUrl();
    
    // Agar URL berilmagan bo'lsa, joriy URL ni olish
    if ($url === null) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                     (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
                     ? 'https://' : 'http://';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        // Query string ni saqlab qolamiz (timestamp bo'lishi mumkin)
        $url = $protocol . $_SERVER['HTTP_HOST'] . $requestUri;
    }
    
    // Telegram cache uchun timestamp qo'shish
    // Agar URL'da allaqachon timestamp bo'lsa, uni saqlab qolamiz
    // Agar yo'q bo'lsa, yangi timestamp qo'shamiz
    if ($addCacheBuster) {
        $hasTimestamp = (strpos($url, '?v=') !== false || strpos($url, '&v=') !== false || 
                         strpos($url, '?t=') !== false || strpos($url, '&t=') !== false);
        $url = addCacheBuster($url, false); // Eski timestamp bo'lsa, uni saqlab qolamiz
    }
    
    // Agar image berilmagan bo'lsa, default image
    if ($image === null) {
        // Default image yo'q bo'lsa, site logo yoki favicon ishlatamiz
        $image = $baseUrl . '/assets/images/og-image.png';
    } else {
        // Agar image relative path bo'lsa, absolute qilamiz
        if (strpos($image, 'http') !== 0) {
            $image = $baseUrl . '/' . ltrim($image, '/');
        }
    }
    
    $siteName = 'Anonim So\'rovnoma';
    $locale = getUserLanguage() === 'ru' ? 'ru_RU' : 'uz_UZ';
    
    // Meta teglarni yaratish
    $meta = '';
    $meta .= '<meta property="og:title" content="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    $meta .= '<meta property="og:description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    $meta .= '<meta property="og:image" content="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    $meta .= '<meta property="og:image:width" content="1200">' . "\n";
    $meta .= '<meta property="og:image:height" content="630">' . "\n";
    $meta .= '<meta property="og:image:type" content="image/png">' . "\n";
    $meta .= '<meta property="og:url" content="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    $meta .= '<meta property="og:type" content="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    $meta .= '<meta property="og:site_name" content="' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    $meta .= '<meta property="og:locale" content="' . htmlspecialchars($locale, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    
    // Twitter Card
    $meta .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $meta .= '<meta name="twitter:title" content="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    $meta .= '<meta name="twitter:description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    $meta .= '<meta name="twitter:image" content="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    
    return $meta;
}
?>

