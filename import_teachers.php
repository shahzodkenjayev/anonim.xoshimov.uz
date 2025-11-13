<?php
/**
 * Excel fayldan o'qituvchilarni import qilish skripti
 * 
 * Talablar:
 * - PHPExcel yoki PhpSpreadsheet kutubxonasi kerak
 * - Yoki CSV formatiga o'tkazib import qilish
 * 
 * Foydalanish:
 * 1. Excel faylni CSV formatiga o'tkazing
 * 2. Yoki PhpSpreadsheet o'rnating: composer require phpoffice/phpspreadsheet
 * 3. Bu skriptni ishga tushiring
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Admin tekshirish
if (!isLoggedIn() || !isAdmin()) {
    die("Bu sahifa faqat adminlar uchun!");
}

$conn = getDBConnection();
$message = '';
$message_type = '';
$imported_count = 0;
$errors = [];

// Excel fayl nomi
$excel_file = 'teachers.xlsx';
$csv_file = 'teachers.csv';

// Variant 1: CSV fayldan import (eng oson)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $file['tmp_name'];
        $handle = fopen($tmp_name, 'r');
        
        if ($handle !== false) {
            $conn->beginTransaction();
            $line_number = 0;
            $delimiter = ',';
            
            try {
                // Birinchi qatorni o'qib, delimiter'ni aniqlash
                $first_line = fgets($handle);
                rewind($handle);
                
                // Delimiter'ni aniqlash (vergul, nuqta-vergul, tab)
                if (strpos($first_line, ';') !== false && substr_count($first_line, ';') >= 2) {
                    $delimiter = ';';
                } elseif (strpos($first_line, "\t") !== false && substr_count($first_line, "\t") >= 2) {
                    $delimiter = "\t";
                }
                
                // Birinchi qatorni o'tkazib yuborish (header)
                $header = fgetcsv($handle, 0, $delimiter);
                
                // Header'ni tekshirish
                if (count($header) < 3) {
                    $message = "CSV fayl formati noto'g'ri! Kamida 3 ta ustun bo'lishi kerak (Ism, Kafedra UZ, Kafedra RU).";
                    $message_type = 'error';
                } else {
                    while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                        $line_number++;
                        
                        // Bo'sh qatorlarni o'tkazib yuborish
                        if (empty(array_filter($data))) {
                            continue;
                        }
                        
                        if (count($data) < 3) {
                            $errors[] = "Qator $line_number: Yetarli ma'lumot yo'q (topildi: " . count($data) . " ta ustun, kerak: 3 ta). Ma'lumotlar: " . implode(' | ', $data);
                            continue;
                        }
                        
                        $full_name = trim($data[0] ?? '');
                        $department_uz = trim($data[1] ?? '');
                        $department_ru = trim($data[2] ?? '');
                        
                        if (empty($full_name)) {
                            $errors[] = "Qator $line_number: Ism bo'sh";
                            continue;
                        }
                        
                        // Xodim mavjudligini tekshirish
                        $check_stmt = $conn->prepare("SELECT id FROM employees WHERE full_name = ?");
                        $check_stmt->execute([$full_name]);
                        $exists = $check_stmt->fetch();
                        
                        if ($exists) {
                            $errors[] = "Qator $line_number: '$full_name' allaqachon mavjud";
                            continue;
                        }
                        
                        // Xodimni qo'shish (teacher pozitsiyasi)
                        $insert_stmt = $conn->prepare("INSERT INTO employees (full_name, position, department_uz, department_ru) VALUES (?, 'teacher', ?, ?)");
                        $insert_stmt->execute([$full_name, $department_uz, $department_ru]);
                        
                        $imported_count++;
                    }
                    
                    $conn->commit();
                    if ($imported_count > 0) {
                        $message = "Muvaffaqiyatli! $imported_count ta o'qituvchi import qilindi.";
                        $message_type = 'success';
                    } else {
                        $message = "Hech qanday ma'lumot import qilinmadi. CSV fayl formatini tekshiring.";
                        $message_type = 'error';
                    }
                }
                
            } catch (Exception $e) {
                $conn->rollBack();
                $message = "Xatolik: " . $e->getMessage();
                $message_type = 'error';
            }
            
            fclose($handle);
        }
    } else {
        $message = "Fayl yuklashda xatolik!";
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>O'qituvchilarni Import Qilish</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>O'qituvchilarni Import Qilish</h1>
            <div class="user-info">
                <a href="admin/dashboard.php" class="btn btn-secondary">← Admin Panel</a>
                <a href="logout.php" class="btn btn-secondary">Chiqish</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
                <?php if ($imported_count > 0): ?>
                    <p><strong>Import qilingan:</strong> <?php echo $imported_count; ?> ta o'qituvchi</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Xatoliklar:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="admin-section">
            <h2>CSV Fayldan Import</h2>
            <p><strong>Talablar:</strong></p>
            <ol>
                <li>Excel faylni CSV formatiga o'tkazing: <strong>File > Save As > CSV UTF-8 (Comma delimited)</strong></li>
                <li>CSV fayl format: <code>A ustun: Ism, B ustun: Kafedra (UZ), C ustun: Kafedra (RU)</code></li>
                <li>Birinchi qator header bo'lishi kerak (o'tkazib yuboriladi)</li>
                <li><strong>Muhim:</strong> CSV fayl UTF-8 encoding'da bo'lishi kerak!</li>
            </ol>
            
            <div class="alert alert-info">
                <strong>CSV Fayl Format Namunasi:</strong><br>
                <code style="display: block; padding: 10px; background: #f0f0f0; border-radius: 5px; margin-top: 10px;">
                Full Name,Kafedra (UZ),Kafedra (RU)<br>
                Abdullayev Alisher Valiyevich,Axborot texnologiyalari,Информационные технологии<br>
                Karimov Bahodir Toshmatovich,Dasturlash,Программирование
                </code>
            </div>
            
            <div class="alert alert-info">
                <strong>⚠️ CSV Fayl Formatini Tekshirish:</strong><br>
                Agar import ishlamasa, <a href="test_csv.php" target="_blank">test_csv.php</a> faylini ishlatib, CSV fayl formatini tekshiring.
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label for="csv_file">CSV Fayl Tanlang:</label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                    <small>Faqat CSV fayllar (.csv). UTF-8 encoding bo'lishi kerak.</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Import Qilish</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>SQL Buyruqlar (Qo'lda Import)</h2>
            <p>Agar CSV import ishlamasa, Excel faylni ochib, quyidagi formatda SQL buyruqlar yarating:</p>
            
            <div class="code-block">
                <pre><code>-- Excel fayldan SQL buyruqlar
-- Format: A ustun = full_name, B ustun = department_uz, C ustun = department_ru

INSERT INTO employees (full_name, position, department_uz, department_ru) VALUES
('Ism Familiya', 'teacher', 'Kafedra nomi (UZ)'),
('Ism Familiya 2', 'teacher', 'Kafedra nomi 2 (UZ)'),
-- ... va hokazo
;</code></pre>
            </div>
            
            <p><strong>Eslatma:</strong> Har bir qator uchun alohida INSERT buyrug'i yozing yoki bir nechta qatorni birga qo'shing.</p>
        </div>
        
        <div class="admin-section">
            <h2>Mavjud O'qituvchilar</h2>
            <?php
            $teachers_query = "SELECT id, full_name, department_uz, department_ru FROM employees WHERE position = 'teacher' ORDER BY full_name";
            $teachers_result = $conn->query($teachers_query);
            $teachers = $teachers_result->fetchAll();
            ?>
            
            <p><strong>Jami:</strong> <?php echo count($teachers); ?> ta o'qituvchi</p>
            
            <?php if (count($teachers) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ism</th>
                            <th>Kafedra</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teachers as $teacher): ?>
                            <tr>
                                <td><?php echo $teacher['id']; ?></td>
                                <td><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['department_uz'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">Hozircha o'qituvchilar ro'yxati bo'sh.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

