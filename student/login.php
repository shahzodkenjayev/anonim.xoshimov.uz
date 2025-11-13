<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Agar login qilgan bo'lsa, tegishli sahifaga yo'naltirish
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: survey.php');
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
            // Faqat talabalar kirishi mumkin
            if ($user['role'] === 'admin') {
                $error = 'Adminlar uchun alohida login sahifasi mavjud. <a href="../admin/login.php">Admin login</a>';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: survey.php');
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
    <title>Talaba - Tizimga Kirish</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Talaba Tizimiga Kirish</h1>
            <h2>Fikringizni bildiring</h2>
            
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
            
            <div class="login-links">
                <p><a href="../index.php">← Asosiy sahifaga qaytish</a></p>
                <p><a href="../anonymous.php">Anonim ovoz berish</a></p>
            </div>
        </div>
    </div>
</body>
</html>

