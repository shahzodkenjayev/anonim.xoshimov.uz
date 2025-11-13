<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin tekshirish
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../index.php');
    exit;
}

$employee_id = intval($_GET['id'] ?? 0);

if ($employee_id <= 0) {
    header('Location: dashboard.php');
    exit;
}

$conn = getDBConnection();

// Xodim ma'lumotlari
$employee_stmt = $conn->prepare("SELECT id, full_name, position, department FROM employees WHERE id = ?");
$employee_stmt->execute([$employee_id]);
$employee = $employee_stmt->fetch();

if (!$employee) {
    header('Location: dashboard.php');
    exit;
}

// Savollar va javoblar
$results_query = "SELECT 
    q.id as question_id,
    q.question_text,
    q.question_type,
    AVG(sr.rating) as avg_rating,
    COUNT(sr.id) as response_count,
    GROUP_CONCAT(sr.text_response SEPARATOR '|||') as text_responses
    FROM questions q
    LEFT JOIN survey_responses sr ON q.id = sr.question_id AND sr.employee_id = ?
    WHERE q.position_type = 'all' OR q.position_type = ?
    GROUP BY q.id
    ORDER BY q.id";

$stmt = $conn->prepare($results_query);
$stmt->execute([$employee_id, $employee['position']]);
$results = $stmt->fetchAll();

// Umumiy statistika
$stats_query = "SELECT 
    COUNT(DISTINCT survey_submissions.user_id) as total_responses,
    AVG(sr.rating) as overall_avg
    FROM survey_submissions
    LEFT JOIN survey_responses sr ON survey_submissions.employee_id = sr.employee_id AND sr.rating IS NOT NULL
    WHERE survey_submissions.employee_id = ?";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->execute([$employee_id]);
$stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xodim Natijalari - <?php echo htmlspecialchars($employee['full_name']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Xodim Natijalari</h1>
            <div class="user-info">
                <a href="dashboard.php" class="btn btn-secondary">Orqaga</a>
                <a href="../logout.php" class="btn btn-secondary">Chiqish</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="employee-info-card">
            <h2><?php echo htmlspecialchars($employee['full_name']); ?></h2>
            <p><strong>Lavozim:</strong> <?php echo getPositionName($employee['position']); ?></p>
            <?php if ($employee['department']): ?>
                <p><strong>Bo'lim:</strong> <?php echo htmlspecialchars($employee['department']); ?></p>
            <?php endif; ?>
            <div class="stats-inline">
                <span><strong>Jami javoblar:</strong> <?php echo $stats['total_responses']; ?></span>
                <?php if ($stats['overall_avg']): ?>
                    <span><strong>O'rtacha baho:</strong> <?php echo number_format($stats['overall_avg'], 2); ?> / 5.00</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="results-section">
            <h2>Batafsil natijalar</h2>
            
            <?php foreach ($results as $row): ?>
                <div class="result-card">
                    <h3><?php echo htmlspecialchars($row['question_text']); ?></h3>
                    
                    <?php if ($row['question_type'] === 'rating'): ?>
                        <?php if ($row['response_count'] > 0): ?>
                            <div class="rating-result">
                                <span class="avg-rating"><?php echo number_format($row['avg_rating'], 2); ?></span>
                                <span class="stars"><?php echo renderStars(round($row['avg_rating'])); ?></span>
                                <span class="response-count">(<?php echo $row['response_count']; ?> ta javob)</span>
                            </div>
                        <?php else: ?>
                            <p class="no-data">Hozircha javoblar yo'q</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if ($row['text_responses']): ?>
                            <div class="text-responses">
                                <?php 
                                $texts = explode('|||', $row['text_responses']);
                                foreach ($texts as $text): 
                                    if (!empty(trim($text))):
                                ?>
                                    <div class="text-response-item">
                                        <p><?php echo htmlspecialchars($text); ?></p>
                                    </div>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        <?php else: ?>
                            <p class="no-data">Hozircha javoblar yo'q</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

