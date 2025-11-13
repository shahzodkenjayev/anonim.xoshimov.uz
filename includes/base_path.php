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
?>

