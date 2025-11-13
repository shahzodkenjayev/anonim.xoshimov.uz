<?php
/**
 * CSV fayl formatini tekshirish skripti
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Admin tekshirish
if (!isLoggedIn() || !isAdmin()) {
    die("Bu sahifa faqat adminlar uchun!");
}

$analysis = null;

if (isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $file['tmp_name'];
        
        // Faylni o'qish
        $handle = fopen($tmp_name, 'r');
        $line_number = 0;
        $analysis = [
            'lines' => [],
            'delimiters' => [],
            'total_lines' => 0
        ];
        
        while (($line = fgets($handle)) !== false && $line_number < 10) {
            $line_number++;
            $analysis['total_lines']++;
            
            // Turli delimiter'larni sinab ko'rish
            $data_comma = str_getcsv($line, ',');
            $data_semicolon = str_getcsv($line, ';');
            $data_tab = str_getcsv($line, "\t");
            
            $analysis['lines'][] = [
                'number' => $line_number,
                'raw' => $line,
                'comma' => $data_comma,
                'semicolon' => $data_semicolon,
                'tab' => $data_tab,
                'comma_count' => count($data_comma),
                'semicolon_count' => count($data_semicolon),
                'tab_count' => count($data_tab)
            ];
            
            if ($line_number === 1) {
                $analysis['delimiters'] = [
                    'comma' => count($data_comma),
                    'semicolon' => count($data_semicolon),
                    'tab' => count($data_tab)
                ];
            }
        }
        
        fclose($handle);
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Fayl Tahlili</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>CSV Fayl Formatini Tekshirish</h1>
            <div class="user-info">
                <a href="import_teachers.php" class="btn btn-secondary">← Import sahifaga</a>
                <a href="admin/dashboard.php" class="btn btn-secondary">Admin Panel</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="admin-section">
            <h2>CSV Fayl Yuklash</h2>
            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label for="csv_file">CSV Fayl Tanlang:</label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                </div>
                <button type="submit" class="btn btn-primary">Tahlil Qilish</button>
            </form>
        </div>
        
        <?php if ($analysis): ?>
            <div class="admin-section">
                <h2>Tahlil Natijalari</h2>
                
                <div class="alert alert-info">
                    <strong>Jami qatorlar:</strong> <?php echo $analysis['total_lines']; ?><br>
                    <strong>Tavsiya etiladigan delimiter:</strong> 
                    <?php
                    $best = 'comma';
                    $best_count = $analysis['delimiters']['comma'];
                    if ($analysis['delimiters']['semicolon'] > $best_count) {
                        $best = 'semicolon';
                        $best_count = $analysis['delimiters']['semicolon'];
                    }
                    if ($analysis['delimiters']['tab'] > $best_count) {
                        $best = 'tab';
                        $best_count = $analysis['delimiters']['tab'];
                    }
                    echo $best . " (" . $best_count . " ta ustun)";
                    ?>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Qator</th>
                            <th>Vergul (,)</th>
                            <th>Nuqta-vergul (;)</th>
                            <th>Tab</th>
                            <th>Ma'lumotlar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analysis['lines'] as $line_data): ?>
                            <tr>
                                <td><?php echo $line_data['number']; ?></td>
                                <td><?php echo $line_data['comma_count']; ?> ta</td>
                                <td><?php echo $line_data['semicolon_count']; ?> ta</td>
                                <td><?php echo $line_data['tab_count']; ?> ta</td>
                                <td>
                                    <small>
                                        <?php 
                                        $best_data = $line_data['comma'];
                                        if ($line_data['semicolon_count'] > count($best_data)) {
                                            $best_data = $line_data['semicolon'];
                                        }
                                        if ($line_data['tab_count'] > count($best_data)) {
                                            $best_data = $line_data['tab'];
                                        }
                                        echo htmlspecialchars(implode(' | ', array_slice($best_data, 0, 3)));
                                        ?>
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($analysis['lines'][0]['comma_count'] < 3 && $analysis['lines'][0]['semicolon_count'] < 3 && $analysis['lines'][0]['tab_count'] < 3): ?>
                    <div class="alert alert-error">
                        <strong>⚠️ Muammo:</strong> CSV faylda kamida 3 ta ustun bo'lishi kerak!<br>
                        Hozirgi holat: <?php echo max($analysis['lines'][0]['comma_count'], $analysis['lines'][0]['semicolon_count'], $analysis['lines'][0]['tab_count']); ?> ta ustun topildi.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

