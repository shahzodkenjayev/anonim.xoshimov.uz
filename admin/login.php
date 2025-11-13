<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Agar login qilgan bo'lsa, tegishli sahifaga yo'naltirish
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: dashboard.php');
    } else {
        header('Location: ../student/survey.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, username, password, full_name, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Faqat adminlar kirishi mumkin
            if ($user['role'] !== 'admin') {
                $error = 'Bu sahifa faqat adminlar uchun. <a href="../student/login.php">Talaba login</a>';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: dashboard.php');
                exit;
            }
        } else {
            $error = 'Noto\'g\'ri foydalanuvchi nomi yoki parol!';
        }
    } else {
        $error = 'Barcha maydonlarni to\'ldiring!';
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Kirish</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Admin Panel</h1>
            <h2>Tizimga kirish</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Foydalanuvchi nomi:</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Parol:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Kirish</button>
            </form>
            
            <div class="login-info">
                <p><strong>Test foydalanuvchi:</strong></p>
                <p>Admin: admin / admin123</p>
            </div>
            
            <div class="login-links">
                <p><a href="../index.php">← Asosiy sahifaga qaytish</a></p>
            </div>
        </div>
    </div>
</body>
</html>

