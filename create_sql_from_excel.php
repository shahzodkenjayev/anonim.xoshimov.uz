<?php
/**
 * Excel fayldan SQL buyruqlar yaratish skripti
 * 
 * Bu skript Excel faylni o'qib, SQL INSERT buyruqlarini yaratadi.
 * CSV fayldan ishlaydi (Excel'ni CSV formatiga o'tkazing).
 */

// CSV fayl nomi
$csv_file = 'teachers.csv';
$output_file = 'import_teachers.sql';

if (!file_exists($csv_file)) {
    die("CSV fayl topilmadi: $csv_file\nExcel faylni CSV formatiga o'tkazing!");
}

$handle = fopen($csv_file, 'r');
if ($handle === false) {
    die("CSV faylni ochib bo'lmadi!");
}

$sql_commands = [];
$sql_commands[] = "-- O'qituvchilarni import qilish SQL buyruqlari";
$sql_commands[] = "-- Excel fayldan yaratilgan: " . date('Y-m-d H:i:s');
$sql_commands[] = "";
$sql_commands[] = "USE anonim_survey;";
$sql_commands[] = "";

// Birinchi qatorni o'tkazib yuborish (header)
fgetcsv($handle);

$line_number = 0;
$success_count = 0;

while (($data = fgetcsv($handle, 1000, ',')) !== false) {
    $line_number++;
    
    if (count($data) < 3) {
        echo "Qator $line_number: Yetarli ma'lumot yo'q (3 ta ustun kerak)\n";
        continue;
    }
    
    $full_name = trim($data[0] ?? '');
    $department_uz = trim($data[1] ?? '');
    $department_ru = trim($data[2] ?? ''); // Rus tilidagi nom ishlatilmaydi, lekin saqlanadi
    
    if (empty($full_name)) {
        echo "Qator $line_number: Ism bo'sh, o'tkazib yuborildi\n";
        continue;
    }
    
    // SQL injectiondan himoya
    $full_name = addslashes($full_name);
    $department_uz = addslashes($department_uz);
    
    // SQL buyrug'ini yaratish
    $department_ru_escaped = addslashes($department_ru);
    $sql = "INSERT INTO employees (full_name, position, department_uz, department_ru) VALUES ('$full_name', 'teacher', '$department_uz', '$department_ru_escaped');";
    $sql_commands[] = $sql;
    
    $success_count++;
}

fclose($handle);

// SQL faylga yozish
$output = implode("\n", $sql_commands);
file_put_contents($output_file, $output);

echo "✅ Muvaffaqiyatli!\n";
echo "📊 Jami qatorlar: $line_number\n";
echo "✅ SQL buyruqlar: $success_count\n";
echo "📁 SQL fayl yaratildi: $output_file\n";
echo "\n";
echo "Keyingi qadamlar:\n";
echo "1. $output_file faylni tekshiring\n";
echo "2. Database'ga import qiling:\n";
echo "   mysql -u root -p anonim_survey < $output_file\n";
echo "   yoki phpMyAdmin orqali import qiling\n";

