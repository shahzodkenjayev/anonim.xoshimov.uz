<?php
// .env faylni yuklash
require_once __DIR__ . '/env_loader.php';

// Database konfiguratsiyasi (.env fayldan o'qiladi)
// Agar .env faylda mavjud bo'lmasa, default qiymatlar ishlatiladi
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'anonim_survey');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
define('DB_TIMEOUT', 10); // Connection timeout (sekundlarda)

// Application settings
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true' || getenv('APP_DEBUG') === '1');

// Database ulanishi (PDO - xavfsiz)
function getDBConnection() {
    try {
        // Avval database nomisiz ulanishga harakat qilamiz (agar database mavjud bo'lmasa)
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => DB_TIMEOUT,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        
        // Avval serverga ulanish
        $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        // Database mavjudligini tekshirish
        $stmt = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
        $db_exists = $stmt->fetch();
        
        if (!$db_exists) {
            // Database yaratishga harakat qilamiz
            try {
                $conn->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            } catch (PDOException $e) {
                die("Database yaratib bo'lmadi. Iltimos, database'ni qo'lda yarating.<br>Xatolik: " . $e->getMessage());
            }
        }
        
        // Endi database'ga ulanish
        $dsn_with_db = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $conn = new PDO($dsn_with_db, DB_USER, DB_PASS, $options);
        
        return $conn;
    } catch (PDOException $e) {
        $error_msg = "Database ulanish xatosi!<br><br>";
        $error_msg .= "<strong>Xatolik:</strong> " . $e->getMessage() . "<br>";
        $error_msg .= "<strong>Server:</strong> " . DB_HOST . ":" . DB_PORT . "<br>";
        $error_msg .= "<strong>Database:</strong> " . DB_NAME . "<br>";
        $error_msg .= "<strong>Foydalanuvchi:</strong> " . DB_USER . "<br><br>";
        $error_msg .= "<strong>Tekshirish kerak:</strong><br>";
        $error_msg .= "1. Database nomi to'g'rimi?<br>";
        $error_msg .= "2. MySQL foydalanuvchisi remote ulanish huquqiga egami?<br>";
        $error_msg .= "3. Firewall 3306 portini ochiq qilganmi?<br>";
        $error_msg .= "4. MySQL server remote ulanishlarga ruxsat bermayaptimi?";
        
        die($error_msg);
    }
}

// Session boshlash
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

