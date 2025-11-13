<?php
// Database konfiguratsiyasi - Namuna fayl
// Bu faylni nusxalab database.php qiling va ma'lumotlarni to'ldiring

// Serverda ishlatish uchun (localhost):
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', 'sizning_mysql_parolingiz');
define('DB_NAME', 'anonim_survey');

// Remote server uchun (agar kerak bo'lsa):
// define('DB_HOST', '206.189.114.116');
// define('DB_PORT', '3306');
// define('DB_USER', 'root');
// define('DB_PASS', 'Amina2021.206');
// define('DB_NAME', 'anonim_survey');

define('DB_CHARSET', 'utf8mb4');
define('DB_TIMEOUT', 10);

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
        
        if (DB_HOST === 'localhost') {
            $error_msg .= "<strong>Tekshirish kerak:</strong><br>";
            $error_msg .= "1. MySQL server ishlayotganmi? (sudo systemctl status mysql)<br>";
            $error_msg .= "2. Database nomi to'g'rimi?<br>";
            $error_msg .= "3. MySQL parol to'g'rimi?<br>";
            $error_msg .= "4. Database yaratilganmi? (mysql -u root -p -e 'SHOW DATABASES;')";
        } else {
            $error_msg .= "<strong>Tekshirish kerak:</strong><br>";
            $error_msg .= "1. Remote MySQL server ishlayotganmi?<br>";
            $error_msg .= "2. Firewall 3306 portini ochiq qilganmi?<br>";
            $error_msg .= "3. MySQL remote access yoqilganmi?";
        }
        
        die($error_msg);
    }
}

// Session boshlash
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

