<?php
/**
 * Koordinatorlarni bazaga qo'shish skripti
 * 
 * Bu skript koordinatorlarni bazaga qo'shadi.
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

// Koordinatorlar ro'yxati
$coordinators = [
    [
        'full_name' => 'Bekzod Xayritdinovich',
        'department_uz' => 'Axborot tizimlari va texnologiyalari fakulteti',
        'department_ru' => 'Факультет информационных систем и технологий'
    ],
    [
        'full_name' => 'Quvonch Jumadullayevich',
        'department_uz' => 'Turizm fakulteti',
        'department_ru' => 'Факультет туризма'
    ]
];

$added_count = 0;
$skipped_count = 0;
$errors = [];

echo "<h2>Koordinatorlarni qo'shish</h2>\n";
echo "<div style='font-family: monospace; padding: 20px;'>\n";

foreach ($coordinators as $index => $coordinator) {
    $full_name = trim($coordinator['full_name']);
    $department_uz = trim($coordinator['department_uz']);
    $department_ru = trim($coordinator['department_ru']);
    
    try {
        // Xodim mavjudligini tekshirish
        $check_stmt = $conn->prepare("SELECT id, position, department_uz FROM employees WHERE full_name = ?");
        $check_stmt->execute([$full_name]);
        $exists = $check_stmt->fetch();
        
        if ($exists) {
            // Agar mavjud bo'lsa, koordinator pozitsiyasiga yangilash
            if ($exists['position'] !== 'coordinator') {
                $update_stmt = $conn->prepare("UPDATE employees SET position = 'coordinator', department_uz = ?, department_ru = ? WHERE id = ?");
                $update_stmt->execute([$department_uz, $department_ru, $exists['id']]);
                echo "✅ <strong>$full_name</strong> - Pozitsiya 'coordinator' ga yangilandi<br>\n";
                $added_count++;
            } else {
                // Agar allaqachon koordinator bo'lsa, department ma'lumotlarini yangilash
                $update_stmt = $conn->prepare("UPDATE employees SET department_uz = ?, department_ru = ? WHERE id = ?");
                $update_stmt->execute([$department_uz, $department_ru, $exists['id']]);
                echo "ℹ️ <strong>$full_name</strong> - Ma'lumotlar yangilandi<br>\n";
                $skipped_count++;
            }
        } else {
            // Yangi koordinator qo'shish
            $insert_stmt = $conn->prepare("INSERT INTO employees (full_name, position, department_uz, department_ru) VALUES (?, 'coordinator', ?, ?)");
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

// Koordinatorlar ro'yxatini ko'rsatish
echo "<div style='margin-top: 20px;'>\n";
echo "<h3>Bazadagi koordinatorlar:</h3>\n";
$coordinators_query = "SELECT id, full_name, department_uz, department_ru FROM employees WHERE position = 'coordinator' ORDER BY full_name";
$coordinators_result = $conn->query($coordinators_query);
$all_coordinators = $coordinators_result->fetchAll();

if (count($all_coordinators) > 0) {
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<thead><tr><th>ID</th><th>Ism</th><th>Fakultet (UZ)</th><th>Fakultet (RU)</th></tr></thead>\n";
    echo "<tbody>\n";
    foreach ($all_coordinators as $coordinator) {
        echo "<tr>\n";
        echo "<td>" . $coordinator['id'] . "</td>\n";
        echo "<td>" . htmlspecialchars($coordinator['full_name']) . "</td>\n";
        echo "<td>" . htmlspecialchars($coordinator['department_uz'] ?? '-') . "</td>\n";
        echo "<td>" . htmlspecialchars($coordinator['department_ru'] ?? '-') . "</td>\n";
        echo "</tr>\n";
    }
    echo "</tbody>\n";
    echo "</table>\n";
} else {
    echo "<p>Hozircha koordinatorlar ro'yxati bo'sh.</p>\n";
}
echo "</div>\n";

echo "<div style='margin-top: 20px;'>\n";
echo "<a href='admin/dashboard.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Admin Panelga qaytish</a>\n";
echo "</div>\n";
?>

