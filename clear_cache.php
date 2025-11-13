<?php
/**
 * Cache tozalash skripti
 * Bu fayl server cache'ni tozalash uchun ishlatiladi
 * 
 * Eslatma: Bu faylni production'da o'chirish yoki himoyalash tavsiya etiladi
 */

// Xavfsizlik: Faqat localhost yoki admin IP dan kirishga ruxsat berish
$allowedIPs = ['127.0.0.1', '::1', 'localhost'];
$clientIP = $_SERVER['REMOTE_ADDR'] ?? '';

// Production'da IP tekshiruvini yoqish (ixtiyoriy)
// if (!in_array($clientIP, $allowedIPs) && $_SERVER['HTTP_HOST'] !== 'localhost') {
//     die('Access Denied');
// }

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cache Tozalash</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        button {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        button:hover {
            background: #357abd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Server Cache Tozalash</h1>
        
        <?php
        $results = [];
        
        // 1. PHP OPcache tozalash
        if (function_exists('opcache_reset')) {
            if (opcache_reset()) {
                $results[] = ['type' => 'success', 'message' => 'PHP OPcache muvaffaqiyatli tozalandi'];
            } else {
                $results[] = ['type' => 'error', 'message' => 'PHP OPcache tozalashda xatolik (ehtimol cache yo\'q)'];
            }
        } else {
            $results[] = ['type' => 'info', 'message' => 'PHP OPcache mavjud emas'];
        }
        
        // 2. PHP OPcache invalidate (barcha fayllar uchun)
        if (function_exists('opcache_invalidate')) {
            $files = [
                __DIR__ . '/index.php',
                __DIR__ . '/includes/base_path.php',
                __DIR__ . '/student/login.php',
                __DIR__ . '/student/survey.php',
                __DIR__ . '/student/profile.php',
                __DIR__ . '/anonymous.php',
            ];
            
            $invalidated = 0;
            foreach ($files as $file) {
                if (file_exists($file) && opcache_invalidate($file, true)) {
                    $invalidated++;
                }
            }
            
            if ($invalidated > 0) {
                $results[] = ['type' => 'success', 'message' => "$invalidated ta PHP fayl OPcache'dan olib tashlandi"];
            }
        }
        
        // 3. Session fayllarini tozalash (ixtiyoriy)
        $sessionPath = session_save_path();
        if (empty($sessionPath)) {
            $sessionPath = sys_get_temp_dir();
        }
        
        $results[] = ['type' => 'info', 'message' => "Session path: $sessionPath"];
        
        // 4. Log fayllarini tekshirish
        $logDir = __DIR__ . '/logs';
        if (is_dir($logDir)) {
            $results[] = ['type' => 'info', 'message' => "Log papkasi mavjud: $logDir"];
        }
        
        // 5. Browser cache tozalash uchun tavsiyalar
        $results[] = ['type' => 'info', 'message' => 'Browser cache\'ni tozalash uchun: Ctrl+Shift+Delete (Chrome/Firefox)'];
        
        // 6. Telegram cache muammosi
        $results[] = ['type' => 'info', 'message' => 'Telegram cache\'ni tozalash uchun linkga ?v=' . time() . ' qo\'shing'];
        
        // Natijalarni ko'rsatish
        foreach ($results as $result) {
            $class = $result['type'];
            echo "<div class='$class'>{$result['message']}</div>";
        }
        ?>
        
        <div style="margin-top: 30px;">
            <h2>Qo'shimcha Amallar:</h2>
            <button onclick="location.reload()">🔄 Sahifani Yangilash</button>
            <button onclick="window.location.href='/'">🏠 Asosiy Sahifaga</button>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-radius: 4px;">
            <strong>⚠️ Eslatma:</strong>
            <ul>
                <li>Bu faylni production'da o'chirish yoki himoyalash tavsiya etiladi</li>
                <li>Telegram cache'ni tozalash uchun linkga <code>?v=<?php echo time(); ?></code> qo'shing</li>
                <li>Browser cache'ni tozalash uchun: Ctrl+Shift+Delete</li>
                <li>Server cache'ni tozalash uchun server admin bilan bog'laning</li>
            </ul>
        </div>
    </div>
</body>
</html>

