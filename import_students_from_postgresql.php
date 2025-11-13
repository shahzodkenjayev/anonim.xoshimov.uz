<?php
/**
 * PostgreSQL dan talabalar ma'lumotlarini MySQL ga import qilish
 * 
 * Bu skript PostgreSQL bazadan talabalar ma'lumotlarini o'qib, MySQL bazaga yozadi.
 * Admin panelidan yoki to'g'ridan-to'g'ri ishga tushirish mumkin.
 */

require_once 'config/database.php';
require_once 'config/postgresql.php';
require_once 'includes/functions.php';

// Admin tekshirish (agar web orqali ochilsa)
if (php_sapi_name() !== 'cli') {
    if (!isLoggedIn() || !isAdmin()) {
        die("Bu sahifa faqat adminlar uchun!");
    }
}

$mysql_conn = getDBConnection();
$pg_conn = getPostgreSQLConnection();

$imported_count = 0;
$updated_count = 0;
$skipped_count = 0;
$errors = [];

echo "<h2>Talabalar ma'lumotlarini import qilish</h2>\n";
echo "<div style='font-family: monospace; padding: 20px;'>\n";

try {
    // PostgreSQL dan talabalar ma'lumotlarini olish
    $pg_query = "SELECT student_id_number, fullname, faculty, hemis_group_name 
                 FROM contingent_students 
                 WHERE student_id_number IS NOT NULL 
                 AND student_id_number != '' 
                 ORDER BY student_id_number";
    
    $pg_stmt = $pg_conn->query($pg_query);
    $students = $pg_stmt->fetchAll();
    
    echo "<p><strong>PostgreSQL dan topildi:</strong> " . count($students) . " ta talaba</p>\n";
    echo "<hr>\n";
    
    // Default parol (12345678)
    $default_password = password_hash('12345678', PASSWORD_DEFAULT);
    
    foreach ($students as $student) {
        $hemis_id = trim($student['student_id_number'] ?? '');
        $full_name = trim($student['fullname'] ?? '');
        $faculty = trim($student['faculty'] ?? '');
        $group_name = trim($student['hemis_group_name'] ?? '');
        
        if (empty($hemis_id) || empty($full_name)) {
            $skipped_count++;
            $errors[] = "HEMIS ID yoki ism bo'sh: " . $hemis_id;
            continue;
        }
        
        try {
            // MySQL bazada mavjudligini tekshirish
            $check_stmt = $mysql_conn->prepare("SELECT id, hemis_id FROM users WHERE hemis_id = ? OR username = ?");
            $check_stmt->execute([$hemis_id, $hemis_id]);
            $exists = $check_stmt->fetch();
            
            if ($exists) {
                // Mavjud bo'lsa, yangilash
                $update_stmt = $mysql_conn->prepare("UPDATE users SET 
                    full_name = ?, 
                    faculty = ?, 
                    group_name = ?,
                    role = 'student'
                    WHERE id = ?");
                $update_stmt->execute([$full_name, $faculty, $group_name, $exists['id']]);
                $updated_count++;
                echo "✅ <strong>$hemis_id</strong> - Yangilandi: $full_name<br>\n";
            } else {
                // Yangi talaba qo'shish
                $insert_stmt = $mysql_conn->prepare("INSERT INTO users 
                    (username, hemis_id, password, full_name, faculty, group_name, role) 
                    VALUES (?, ?, ?, ?, ?, ?, 'student')");
                $insert_stmt->execute([$hemis_id, $hemis_id, $default_password, $full_name, $faculty, $group_name]);
                $imported_count++;
                echo "➕ <strong>$hemis_id</strong> - Qo'shildi: $full_name ($faculty, $group_name)<br>\n";
            }
        } catch (PDOException $e) {
            $error_msg = "$hemis_id - Xatolik: " . $e->getMessage();
            $errors[] = $error_msg;
            echo "❌ $error_msg<br>\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ PostgreSQL dan ma'lumot olishda xatolik: " . $e->getMessage() . "<br>\n";
}

echo "</div>\n";

// Natijalar
echo "<div style='margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;'>\n";
echo "<h3>Natijalar:</h3>\n";
echo "<p><strong>Qo'shildi:</strong> $imported_count ta</p>\n";
echo "<p><strong>Yangilandi:</strong> $updated_count ta</p>\n";
if ($skipped_count > 0) {
    echo "<p><strong>O'tkazib yuborildi:</strong> $skipped_count ta</p>\n";
}
if (count($errors) > 0) {
    echo "<p><strong>Xatoliklar:</strong> " . count($errors) . " ta</p>\n";
    if (count($errors) <= 20) {
        echo "<ul>\n";
        foreach ($errors as $error) {
            echo "<li>$error</li>\n";
        }
        echo "</ul>\n";
    }
}
echo "</div>\n";

// Talabalar ro'yxatini ko'rsatish
echo "<div style='margin-top: 20px;'>\n";
echo "<h3>MySQL bazadagi talabalar:</h3>\n";
$students_query = "SELECT id, username, hemis_id, full_name, faculty, group_name FROM users WHERE role = 'student' ORDER BY full_name LIMIT 50";
$students_result = $mysql_conn->query($students_query);
$all_students = $students_result->fetchAll();

if (count($all_students) > 0) {
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<thead><tr><th>ID</th><th>HEMIS ID</th><th>Ism</th><th>Fakultet</th><th>Guruh</th></tr></thead>\n";
    echo "<tbody>\n";
    foreach ($all_students as $student) {
        echo "<tr>\n";
        echo "<td>" . $student['id'] . "</td>\n";
        echo "<td>" . htmlspecialchars($student['hemis_id'] ?? $student['username'] ?? '-') . "</td>\n";
        echo "<td>" . htmlspecialchars($student['full_name']) . "</td>\n";
        echo "<td>" . htmlspecialchars($student['faculty'] ?? '-') . "</td>\n";
        echo "<td>" . htmlspecialchars($student['group_name'] ?? '-') . "</td>\n";
        echo "</tr>\n";
    }
    echo "</tbody>\n";
    echo "</table>\n";
    echo "<p><em>(Faqat birinchi 50 ta talaba ko'rsatilmoqda)</em></p>\n";
} else {
    echo "<p>Hozircha talabalar ro'yxati bo'sh.</p>\n";
}
echo "</div>\n";

echo "<div style='margin-top: 20px;'>\n";
echo "<a href='admin/dashboard.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Admin Panelga qaytish</a>\n";
echo "</div>\n";
?>

