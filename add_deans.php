<?php
/**
 * Dekanlarni bazaga qo'shish skripti
 * 
 * Bu skript dekanlarni bazaga qo'shadi.
 * Admin panelidan yoki to'g'ridan-to'g'ri ishga tushirish mumkin.
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Admin tekshirish (agar web orqali ochilsa)
if (php_sapi_name() !== 'cli') {
    if (!isLoggedIn() || !isAdmin()) {
        die("Bu sahifa faqat adminlar uchun!");
    }
}

$conn = getDBConnection();

// Dekanlar ro'yxati
$deans = [
    [
        'full_name' => 'Kenjayev Shahzod Yangiboyevich',
        'department_uz' => 'Axborot tizimlari va texnologiyalari fakulteti',
        'department_ru' => 'Факультет информационных систем и технологий'
    ],
    [
        'full_name' => 'Nurallayev Baxtiyor Arziqulovich',
        'department_uz' => 'Kechki ta\'lim shakli',
        'department_ru' => 'Заочная форма обучения'
    ],
    [
        'full_name' => 'Nurullayev Baxtiyor Arziqulovich',
        'department_uz' => 'Meditsina yo\'nalishi',
        'department_ru' => 'Медицинское направление'
    ],
    [
        'full_name' => 'Pardayeva Kamola',
        'department_uz' => 'Aniq va ijtimoiy fanlar fakulteti',
        'department_ru' => 'Факультет точных и социальных наук'
    ],
    [
        'full_name' => 'Levkina Maria Fedorovna',
        'department_uz' => 'Boshlang\'ich ta\'lim va mavktabgacha ta\'lim fakulteti',
        'department_ru' => 'Факультет начального и дошкольного образования'
    ],
    [
        'full_name' => 'Utkirov Shohruh Ixtiyorovich',
        'department_uz' => 'Xorijiy tillar fakulteti',
        'department_ru' => 'Факультет иностранных языков'
    ]
];

$added_count = 0;
$skipped_count = 0;
$errors = [];

echo "<h2>Dekanlarni qo'shish</h2>\n";
echo "<div style='font-family: monospace; padding: 20px;'>\n";

foreach ($deans as $index => $dean) {
    $full_name = trim($dean['full_name']);
    $department_uz = trim($dean['department_uz']);
    $department_ru = trim($dean['department_ru']);
    
    try {
        // Xodim mavjudligini tekshirish
        $check_stmt = $conn->prepare("SELECT id, position, department_uz FROM employees WHERE full_name = ?");
        $check_stmt->execute([$full_name]);
        $exists = $check_stmt->fetch();
        
        if ($exists) {
            // Agar mavjud bo'lsa, dekan pozitsiyasiga yangilash
            if ($exists['position'] !== 'dean') {
                $update_stmt = $conn->prepare("UPDATE employees SET position = 'dean', department_uz = ?, department_ru = ? WHERE id = ?");
                $update_stmt->execute([$department_uz, $department_ru, $exists['id']]);
                echo "✅ <strong>$full_name</strong> - Pozitsiya 'dean' ga yangilandi<br>\n";
                $added_count++;
            } else {
                // Agar allaqachon dekan bo'lsa, department ma'lumotlarini yangilash
                $update_stmt = $conn->prepare("UPDATE employees SET department_uz = ?, department_ru = ? WHERE id = ?");
                $update_stmt->execute([$department_uz, $department_ru, $exists['id']]);
                echo "ℹ️ <strong>$full_name</strong> - Ma'lumotlar yangilandi<br>\n";
                $skipped_count++;
            }
        } else {
            // Yangi dekan qo'shish
            $insert_stmt = $conn->prepare("INSERT INTO employees (full_name, position, department_uz, department_ru) VALUES (?, 'dean', ?, ?)");
            $insert_stmt->execute([$full_name, $department_uz, $department_ru]);
            echo "✅ <strong>$full_name</strong> - Muvaffaqiyatli qo'shildi<br>\n";
            $added_count++;
        }
    } catch (PDOException $e) {
        $error_msg = "$full_name - Xatolik: " . $e->getMessage();
        $errors[] = $error_msg;
        echo "❌ $error_msg<br>\n";
    }
}

echo "</div>\n";

// Natijalar
echo "<div style='margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;'>\n";
echo "<h3>Natijalar:</h3>\n";
echo "<p><strong>Qo'shildi/Yangilandi:</strong> $added_count ta</p>\n";
if ($skipped_count > 0) {
    echo "<p><strong>O'tkazib yuborildi:</strong> $skipped_count ta</p>\n";
}
if (count($errors) > 0) {
    echo "<p><strong>Xatoliklar:</strong> " . count($errors) . " ta</p>\n";
    echo "<ul>\n";
    foreach ($errors as $error) {
        echo "<li>$error</li>\n";
    }
    echo "</ul>\n";
}
echo "</div>\n";

// Dekanlar ro'yxatini ko'rsatish
echo "<div style='margin-top: 20px;'>\n";
echo "<h3>Bazadagi dekanlar:</h3>\n";
$deans_query = "SELECT id, full_name, department_uz, department_ru FROM employees WHERE position = 'dean' ORDER BY full_name";
$deans_result = $conn->query($deans_query);
$all_deans = $deans_result->fetchAll();

if (count($all_deans) > 0) {
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<thead><tr><th>ID</th><th>Ism</th><th>Fakultet (UZ)</th><th>Fakultet (RU)</th></tr></thead>\n";
    echo "<tbody>\n";
    foreach ($all_deans as $dean) {
        echo "<tr>\n";
        echo "<td>" . $dean['id'] . "</td>\n";
        echo "<td>" . htmlspecialchars($dean['full_name']) . "</td>\n";
        echo "<td>" . htmlspecialchars($dean['department_uz'] ?? '-') . "</td>\n";
        echo "<td>" . htmlspecialchars($dean['department_ru'] ?? '-') . "</td>\n";
        echo "</tr>\n";
    }
    echo "</tbody>\n";
    echo "</table>\n";
} else {
    echo "<p>Hozircha dekanlar ro'yxati bo'sh.</p>\n";
}
echo "</div>\n";

echo "<div style='margin-top: 20px;'>\n";
echo "<a href='admin/dashboard.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Admin Panelga qaytish</a>\n";
echo "</div>\n";
?>

