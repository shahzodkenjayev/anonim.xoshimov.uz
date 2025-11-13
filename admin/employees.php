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
$message = '';
$message_type = '';

// Xodim qo'shish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $position = sanitize($_POST['position'] ?? '');
    $department_uz = sanitize($_POST['department_uz'] ?? '');
    $department_ru = sanitize($_POST['department_ru'] ?? '');
    
    if (!empty($full_name) && !empty($position)) {
        try {
            $stmt = $conn->prepare("INSERT INTO employees (full_name, position, department_uz, department_ru) VALUES (?, ?, ?, ?)");
            $stmt->execute([$full_name, $position, $department_uz, $department_ru]);
            $message = 'Xodim muvaffaqiyatli qo\'shildi!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Xatolik yuz berdi!';
            $message_type = 'error';
        }
    }
}

// Xodim o'chirish
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Xodim o\'chirildi!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Xatolik yuz berdi!';
        $message_type = 'error';
    }
}

// Xodimlar ro'yxati
$employees_query = "SELECT id, full_name, position, department_uz, department_ru FROM employees ORDER BY position, full_name";
$employees_result = $conn->query($employees_query);
$employees = $employees_result->fetchAll();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xodimlarni Boshqarish</title>
    <link rel="stylesheet" href="<?php echo css('style.css'); ?>">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Xodimlarni Boshqarish</h1>
            <div class="user-info">
                <a href="dashboard" class="btn btn-secondary">Orqaga</a>
                <a href="../logout" class="btn btn-secondary">Chiqish</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="admin-section">
            <h2>Yangi xodim qo'shish</h2>
            <form method="POST" class="form-inline">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <input type="text" name="full_name" placeholder="To'liq ism" required>
                </div>
                <div class="form-group">
                    <select name="position" required>
                        <option value="">Lavozimni tanlang</option>
                        <option value="teacher">O'qituvchi</option>
                        <option value="dean">Dekan</option>
                        <option value="coordinator">Koordinator</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="department_uz" placeholder="Kafedra (UZ)">
                </div>
                <div class="form-group">
                    <input type="text" name="department_ru" placeholder="Kafedra (RU)">
                </div>
                <button type="submit" class="btn btn-primary">Qo'shish</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>Xodimlar ro'yxati</h2>
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
                                <td><?php echo htmlspecialchars($employee['department_uz'] ?? '-'); ?></td>
                                <td>
                                    <a href="employee_results?id=<?php echo $employee['id']; ?>" class="btn btn-small">Natijalar</a>
                                    <a href="?delete=<?php echo $employee['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Rostdan o\'chirmoqchimisiz?')">O'chirish</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">Hozircha xodimlar ro'yxati bo'sh.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

