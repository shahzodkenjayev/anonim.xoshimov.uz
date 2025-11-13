<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/base_path.php';

// Admin tekshirish
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login');
    exit;
}

$conn = getDBConnection();

// Barcha xodimlar va ularning natijalari
// Javoblar sonini hisoblash: survey_submissions jadvalidan (talabalar uchun)
// Eslatma: Anonim javoblar survey_submissions jadvaliga yozilmaydi, shuning uchun survey_responses jadvalidan ham hisoblaymiz
$results_query = "SELECT 
    e.id,
    e.full_name,
    e.position,
    e.department_uz,
    (
        SELECT COUNT(DISTINCT ss.id)
        FROM survey_submissions ss 
        WHERE ss.employee_id = e.id
    ) as total_responses,
    (
        SELECT AVG(sr.rating)
        FROM survey_responses sr
        WHERE sr.employee_id = e.id
        AND sr.rating IS NOT NULL
    ) as avg_rating
    FROM employees e
    ORDER BY e.position, e.full_name";

$results_result = $conn->query($results_query);
$results = $results_result->fetchAll();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcha Natijalar</title>
    <link rel="stylesheet" href="<?php echo css('style.css'); ?>">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Barcha Natijalar</h1>
            <div class="user-info">
                <a href="dashboard" class="btn btn-secondary">Orqaga</a>
                <a href="../logout" class="btn btn-secondary">Chiqish</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="admin-section">
            <h2>Xodimlar natijalari</h2>
            <?php if (count($results) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ism</th>
                            <th>Lavozim</th>
                            <th>Bo'lim</th>
                            <th>Javoblar soni</th>
                            <th>O'rtacha baho</th>
                            <th>Amallar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo getPositionName($row['position']); ?></td>
                                <td><?php echo htmlspecialchars($row['department_uz'] ?? '-'); ?></td>
                                <td><?php echo $row['total_responses']; ?></td>
                                <td>
                                    <?php if ($row['avg_rating']): ?>
                                        <?php echo number_format($row['avg_rating'], 2); ?> / 5.00
                                        <span class="stars-small"><?php echo renderStars(round($row['avg_rating'])); ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="employee_results?id=<?php echo $row['id']; ?>" class="btn btn-small">Batafsil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">Hozircha natijalar yo'q.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

