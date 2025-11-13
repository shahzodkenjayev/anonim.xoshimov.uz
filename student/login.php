<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Agar login qilgan bo'lsa, tegishli sahifaga yo'naltirish
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: ../admin/dashboard');
    } else {
        header('Location: survey');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hemis_id = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($hemis_id) && !empty($password)) {
        $conn = getDBConnection();
        
        // HEMIS ID yoki username bo'yicha qidirish
        $stmt = $conn->prepare("SELECT id, username, hemis_id, password, full_name, faculty, group_name, role FROM users WHERE hemis_id = ? OR username = ?");
        $stmt->execute([$hemis_id, $hemis_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Default parol tekshiruvi (12345678)
            $default_password = '12345678';
            $password_valid = false;
            
            if ($password === $default_password) {
                // Default parol bilan tekshirish
                if (password_verify($default_password, $user['password'])) {
                    $password_valid = true;
                } else {
                    // Agar parol hash noto'g'ri bo'lsa, yangilash
                    $new_hash = password_hash($default_password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update_stmt->execute([$new_hash, $user['id']]);
                    $password_valid = true;
                }
            } else {
                // Oddiy parol tekshiruvi
                $password_valid = password_verify($password, $user['password']);
            }
            
            if ($password_valid) {
                // Faqat talabalar kirishi mumkin
                if ($user['role'] === 'admin') {
                    $error = 'Adminlar uchun alohida login sahifasi mavjud. <a href="../admin/login">Admin login</a>';
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'] ?? $user['hemis_id'];
                    $_SESSION['hemis_id'] = $user['hemis_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['faculty'] = $user['faculty'] ?? '';
                    $_SESSION['group_name'] = $user['group_name'] ?? '';
                    $_SESSION['role'] = $user['role'];
                    
                    header('Location: survey');
                    exit;
                }
            } else {
                $error = 'Noto\'g\'ri parol! Default parol: 12345678';
            }
        } else {
            $error = 'HEMIS ID topilmadi! Iltimos, avval talabalar ma\'lumotlarini import qiling.';
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
                    <label for="username">HEMIS ID:</label>
                    <input type="text" id="username" name="username" placeholder="HEMIS ID ni kiriting" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Parol:</label>
                    <input type="password" id="password" name="password" placeholder="Default: 12345678" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Kirish</button>
            </form>
            
            <div class="login-info">
                <p><strong>Eslatma:</strong></p>
                <p>Default parol: <strong>12345678</strong></p>
            </div>
            
            <div class="login-links">
                <p><a href="../">← Asosiy sahifaga qaytish</a></p>
                <p><a href="../anonymous">Anonim ovoz berish</a></p>
            </div>
        </div>
    </div>
</body>
</html>

