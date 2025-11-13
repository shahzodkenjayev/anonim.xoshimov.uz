<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin tekshirish
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$conn = getDBConnection();

// Xodimlar ro'yxati
$employees_query = "SELECT id, full_name, position, department FROM employees ORDER BY position, full_name";
$employees_result = $conn->query($employees_query);
$employees = $employees_result->fetchAll();

// Umumiy statistika
$stats_query = "SELECT 
    COUNT(DISTINCT employees.id) as total_employees,
    COUNT(DISTINCT survey_submissions.user_id) as total_students,
    COUNT(survey_submissions.id) as total_submissions
    FROM employees
    LEFT JOIN survey_submissions ON employees.id = survey_submissions.employee_id";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Boshqaruv</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Admin Panel</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="employees.php" class="btn btn-secondary">Xodimlar</a>
                <a href="../logout.php" class="btn btn-secondary">Chiqish</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Jami Xodimlar</h3>
                <p class="stat-number"><?php echo $stats['total_employees']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Jami Talabalar</h3>
                <p class="stat-number"><?php echo $stats['total_students']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Jami Topshirilgan</h3>
                <p class="stat-number"><?php echo $stats['total_submissions']; ?></p>
            </div>
        </div>
        
        <div class="admin-section">
            <h2>Xodimlar ro'yxati</h2>
            <a href="employees.php" class="btn btn-primary">Xodimlarni boshqarish</a>
            <a href="add_student.php" class="btn btn-primary">Talabalarni boshqarish</a>
            <a href="results.php" class="btn btn-primary">Natijalarni ko'rish</a>
            <a href="questions.php" class="btn btn-primary">Savollarni boshqarish</a>
        </div>
        
        <div class="admin-section">
            <h2>Xodimlar</h2>
            <?php if (count($employees) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ism</th>
                            <th>Lavozim</th>
                            <th>Bo'lim</th>
                            <th>Amallar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?php echo $employee['id']; ?></td>
                                <td><?php echo htmlspecialchars($employee['full_name']); ?></td>
                                <td><?php echo getPositionName($employee['position']); ?></td>
                                <td><?php echo htmlspecialchars($employee['department'] ?? '-'); ?></td>
                                <td>
                                    <a href="employee_results.php?id=<?php echo $employee['id']; ?>" class="btn btn-small">Natijalar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">Hozircha xodimlar ro'yxati bo'sh. <a href="employees.php">Qo'shish</a></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

