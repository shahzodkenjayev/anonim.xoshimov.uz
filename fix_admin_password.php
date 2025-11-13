<?php
/**
 * Admin parolini to'g'rilash skripti
 * Bu faylni bir marta ishga tushiring
 */

require_once 'config/database.php';

$conn = getDBConnection();

// Admin parolini yangilash
$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Admin mavjudligini tekshirish
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->execute([$username]);
    $admin = $check_stmt->fetch();
    
    if ($admin) {
        // Mavjud admin parolini yangilash
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update_stmt->execute([$hashed_password, $username]);
        echo "✅ Admin paroli muvaffaqiyatli yangilandi!\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
    } else {
        // Yangi admin yaratish
        $insert_stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, 'admin')");
        $insert_stmt->execute([$username, $hashed_password, 'Administrator']);
        echo "✅ Admin muvaffaqiyatli yaratildi!\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
    }
    
    echo "\nEndi admin/login.php sahifasiga kirib, admin / admin123 bilan login qilishingiz mumkin.\n";
    
} catch (PDOException $e) {
    echo "❌ Xatolik: " . $e->getMessage() . "\n";
}

