<?php
// PostgreSQL database ulanishi

// PostgreSQL konfiguratsiyasi (.env fayldan o'qiladi)
define('PG_HOST', getenv('PG_HOST') ?: '89.232.185.121');
define('PG_PORT', getenv('PG_PORT') ?: '5432');
define('PG_DATABASE', getenv('PG_DATABASE') ?: 'kontingent');
define('PG_USER', getenv('PG_USER') ?: 'kontingent');
define('PG_PASS', getenv('PG_PASS') ?: 'kontingent_2025');

// PostgreSQL ulanishi
function getPostgreSQLConnection() {
    try {
        $dsn = "pgsql:host=" . PG_HOST . ";port=" . PG_PORT . ";dbname=" . PG_DATABASE;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 10
        ];
        
        $conn = new PDO($dsn, PG_USER, PG_PASS, $options);
        return $conn;
    } catch (PDOException $e) {
        $error_msg = "PostgreSQL ulanish xatosi!<br><br>";
        $error_msg .= "<strong>Xatolik:</strong> " . $e->getMessage() . "<br>";
        $error_msg .= "<strong>Server:</strong> " . PG_HOST . ":" . PG_PORT . "<br>";
        $error_msg .= "<strong>Database:</strong> " . PG_DATABASE . "<br>";
        $error_msg .= "<strong>Foydalanuvchi:</strong> " . PG_USER . "<br><br>";
        $error_msg .= "<strong>Tekshirish kerak:</strong><br>";
        $error_msg .= "1. PostgreSQL server ishlayaptimi?<br>";
        $error_msg .= "2. IP manzil to'g'rimi?<br>";
        $error_msg .= "3. Firewall 5432 portini ochiq qilganmi?<br>";
        $error_msg .= "4. Foydalanuvchi remote ulanish huquqiga egami?";
        
        die($error_msg);
    }
}
?>

