<?php
/**
 * Base path funksiyalari
 * CSS, JS va boshqa asset fayllar uchun to'g'ri path'lar
 */

// Base URL ni aniqlash
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path = dirname($script);
    
    // Agar admin papkasida bo'lsa, bir daraja yuqoriga chiqamiz
    if (strpos($path, '/admin') !== false) {
        $path = dirname($path);
    }
    
    // Agar student papkasida bo'lsa
    if (strpos($path, '/student') !== false) {
        $path = dirname($path);
    }
    
    // Agar path '/' bo'lsa, bo'sh qoldiramiz
    if ($path === '/' || $path === '\\') {
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

// Open Graph meta teglarini yaratish
function generateMetaTags($title, $description, $image = null, $type = 'website', $url = null) {
    $baseUrl = getBaseUrl();
    
    // Agar URL berilmagan bo'lsa, joriy URL ni olish
    if ($url === null) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    
    // Agar image berilmagan bo'lsa, default image
    if ($image === null) {
        $image = $baseUrl . '/assets/images/og-image.png'; // Default image
    } else {
        // Agar image relative path bo'lsa, absolute qilamiz
        if (strpos($image, 'http') !== 0) {
            $image = $baseUrl . '/' . ltrim($image, '/');
        }
    }
    
    $siteName = 'Anonim So\'rovnoma';
    $locale = getUserLanguage() === 'ru' ? 'ru_RU' : 'uz_UZ';
    
    $meta = '';
    $meta .= '<meta property="og:title" content="' . htmlspecialchars($title) . '">' . "\n";
    $meta .= '<meta property="og:description" content="' . htmlspecialchars($description) . '">' . "\n";
    $meta .= '<meta property="og:image" content="' . htmlspecialchars($image) . '">' . "\n";
    $meta .= '<meta property="og:url" content="' . htmlspecialchars($url) . '">' . "\n";
    $meta .= '<meta property="og:type" content="' . htmlspecialchars($type) . '">' . "\n";
    $meta .= '<meta property="og:site_name" content="' . htmlspecialchars($siteName) . '">' . "\n";
    $meta .= '<meta property="og:locale" content="' . htmlspecialchars($locale) . '">' . "\n";
    
    // Twitter Card
    $meta .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $meta .= '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">' . "\n";
    $meta .= '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">' . "\n";
    $meta .= '<meta name="twitter:image" content="' . htmlspecialchars($image) . '">' . "\n";
    
    return $meta;
}
?>

