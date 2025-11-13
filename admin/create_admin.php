<?php
/**
 * Admin yaratish yoki parolni yangilash skripti
 * Bu faylni bir marta ishga tushiring, keyin o'chiring
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/base_path.php';

// Tilni o'rnatish (GET parametri orqali)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['uz', 'ru'])) {
    setUserLanguage($_GET['lang']);
}
$current_lang = getUserLanguage();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Admin yaratish yoki yangilash
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    
    if (!empty($username) && !empty($password) && !empty($full_name)) {
        // Parolni hash qilish
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Admin mavjudligini tekshirish
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->execute([$username]);
        $exists = $check_stmt->fetch();
        
        if ($exists) {
            // Mavjud admin parolini yangilash
            try {
                $update_stmt = $conn->prepare("UPDATE users SET password = ?, full_name = ?, role = 'admin' WHERE username = ?");
                $update_stmt->execute([$hashed_password, $full_name, $username]);
                $message = 'Admin paroli muvaffaqiyatli yangilandi!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Xatolik: ' . $e->getMessage();
                $message_type = 'error';
            }
        } else {
            // Yangi admin yaratish
            try {
                $insert_stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, 'admin')");
                $insert_stmt->execute([$username, $hashed_password, $full_name]);
                $message = 'Admin muvaffaqiyatli yaratildi!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Xatolik: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } else {
        $message = 'Barcha maydonlarni to\'ldiring!';
        $message_type = 'error';
    }
}

// Mavjud adminlarni ko'rsatish
$admins_query = "SELECT id, username, full_name, created_at FROM users WHERE role = 'admin' ORDER BY created_at DESC";
$admins_result = $conn->query($admins_query);
$admins = $admins_result->fetchAll();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Yaratish</title>
    <link rel="stylesheet" href="<?php echo css('style.css'); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>Admin Yaratish yoki Parolni Yangilash</h1>
            </div>
            
            <div class="admin-content">
        <div class="admin-section">
            <h1>Admin Yaratish yoki Parolni Yangilash</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="admin-section">
                <h2>Yangi Admin Yaratish yoki Parolni Yangilash</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Foydalanuvchi nomi:</label>
                        <input type="text" id="username" name="username" value="admin" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Parol:</label>
                        <input type="password" id="password" name="password" value="admin123" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">To'liq ism:</label>
                        <input type="text" id="full_name" name="full_name" value="Administrator" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Yaratish/Yangilash</button>
                </form>
            </div>
            
            <div class="admin-section">
                <h2>Mavjud Adminlar</h2>
                <?php if (count($admins) > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Foydalanuvchi nomi</th>
                                <th>To'liq ism</th>
                                <th>Yaratilgan sana</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?php echo $admin['id']; ?></td>
                                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($admin['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">Hozircha adminlar mavjud emas.</div>
                <?php endif; ?>
            </div>
            
            <div class="admin-section">
                <div class="alert alert-info">
                    <strong>⚠️ Xavfsizlik:</strong> Bu faylni admin yaratilgandan keyin o'chiring yoki .htaccess orqali himoyalang!
                </div>
            </div>
            </div>
        </main>
    </div>
</body>
</html>

