<?php
/**
 * .env faylni yuklash va o'qish funksiyasi
 */

function loadEnv($filePath = null) {
    if ($filePath === null) {
        $filePath = __DIR__ . '/../.env';
    }
    
    if (!file_exists($filePath)) {
        // .env fayl mavjud bo'lmasa, .env.example'dan nusxalash
        $examplePath = __DIR__ . '/../.env.example';
        if (file_exists($examplePath)) {
            copy($examplePath, $filePath);
        } else {
            return false;
        }
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Kommentlarni o'tkazib yuborish
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Key=Value formatini ajratish
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Qo'shtirnoqlarni olib tashlash
            $value = trim($value, '"\'');
            
            // Environment variable sifatida o'rnatish (agar mavjud bo'lmasa)
            if (!array_key_exists($key, $_ENV) && !array_key_exists($key, $_SERVER)) {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
    
    return true;
}

// .env faylni yuklash
loadEnv();

