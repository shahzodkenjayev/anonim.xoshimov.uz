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

// Parolni yangilash
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = 'Barcha maydonlarni to\'ldiring!';
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Yangi parollar mos kelmaydi!';
        $message_type = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = 'Parol kamida 6 ta belgidan iborat bo\'lishi kerak!';
        $message_type = 'error';
    } else {
        // Joriy parolni tekshirish
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($current_password, $user['password'])) {
            // Parolni yangilash
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->execute([$hashed_password, $_SESSION['user_id']]);
            
            $message = 'Parol muvaffaqiyatli yangilandi!';
            $message_type = 'success';
        } else {
            $message = 'Joriy parol noto\'g\'ri!';
            $message_type = 'error';
        }
    }
}

// Foydalanuvchi ma'lumotlarini olish
$user_stmt = $conn->prepare("SELECT id, username, full_name, role, created_at FROM users WHERE id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sozlamalar</title>
    <link rel="stylesheet" href="<?php echo css('style.css'); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>Sozlamalar</h1>
            </div>
            
            <div class="admin-content">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <!-- Foydalanuvchi ma'lumotlari -->
                <div class="admin-section">
                    <h2>Foydalanuvchi Ma'lumotlari</h2>
                    <div class="user-info-card">
                        <div class="info-row">
                            <strong>To'liq ism:</strong>
                            <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Foydalanuvchi nomi:</strong>
                            <span><?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Rol:</strong>
                            <span><?php echo $user['role'] === 'admin' ? 'Administrator' : 'Talaba'; ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Hisob yaratilgan sana:</strong>
                            <span><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Parolni yangilash -->
                <div class="admin-section">
                    <h2>Parolni Yangilash</h2>
                    <form method="POST" class="password-form">
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-group">
                            <label for="current_password">Joriy parol:</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Yangi parol:</label>
                            <input type="password" id="new_password" name="new_password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Yangi parolni tasdiqlash:</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-primary">Parolni yangilash</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

