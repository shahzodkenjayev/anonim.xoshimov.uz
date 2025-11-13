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
// Javoblar sonini hisoblash:
// 1. survey_submissions jadvalidan (talabalar javoblari)
// 2. survey_responses jadvalidan (anonim javoblar - vaqt bo'yicha guruhlab)
$results_query = "SELECT 
    e.id,
    e.full_name,
    e.position,
    e.department_uz,
    (
        SELECT COUNT(DISTINCT ss.id)
        FROM survey_submissions ss 
        WHERE ss.employee_id = e.id
    ) + 
    (
        SELECT COUNT(DISTINCT DATE(sr.submitted_at), TIME(sr.submitted_at) DIV 60)
        FROM survey_responses sr
        WHERE sr.employee_id = e.id
        AND NOT EXISTS (
            SELECT 1 FROM survey_submissions ss2
            WHERE ss2.employee_id = sr.employee_id
            AND DATE(ss2.submitted_at) = DATE(sr.submitted_at)
            AND ABS(TIMESTAMPDIFF(SECOND, ss2.submitted_at, sr.submitted_at)) <= 60
        )
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
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>Barcha Natijalar</h1>
            </div>
            
            <div class="admin-content">
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
        </main>
    </div>
</body>
</html>

