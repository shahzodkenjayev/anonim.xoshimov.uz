<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin tekshirish
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$conn = getDBConnection();
$message = '';
$message_type = '';

// Talaba qo'shish
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    
    if (!empty($username) && !empty($password) && !empty($full_name)) {
        // Username mavjudligini tekshirish
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->execute([$username]);
        $check_result = $check_stmt->fetch();
        
        if ($check_result) {
            $message = 'Bu foydalanuvchi nomi allaqachon mavjud!';
            $message_type = 'error';
        } else {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, 'student')");
                $stmt->execute([$username, $hashed_password, $full_name]);
                $message = 'Talaba muvaffaqiyatli qo\'shildi!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Xatolik yuz berdi!';
                $message_type = 'error';
            }
        }
    } else {
        $message = 'Barcha maydonlarni to\'ldiring!';
        $message_type = 'error';
    }
}

// Talabalar ro'yxati
$students_query = "SELECT id, username, full_name, created_at FROM users WHERE role = 'student' ORDER BY created_at DESC";
$students_result = $conn->query($students_query);
$students = $students_result->fetchAll();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talabalarni Boshqarish</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Talabalarni Boshqarish</h1>
            <div class="user-info">
                <a href="dashboard.php" class="btn btn-secondary">Orqaga</a>
                <a href="../logout.php" class="btn btn-secondary">Chiqish</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="admin-section">
            <h2>Yangi talaba qo'shish</h2>
            <form method="POST" class="form-inline">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Foydalanuvchi nomi" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Parol" required>
                </div>
                <div class="form-group">
                    <input type="text" name="full_name" placeholder="To'liq ism" required>
                </div>
                <button type="submit" class="btn btn-primary">Qo'shish</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>Talabalar ro'yxati</h2>
            <?php if (count($students) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Foydalanuvchi nomi</th>
                            <th>To'liq ism</th>
                            <th>Qo'shilgan sana</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo $student['id']; ?></td>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($student['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">Hozircha talabalar ro'yxati bo'sh.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

