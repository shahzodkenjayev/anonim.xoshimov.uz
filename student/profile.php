<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/base_path.php';

// Login tekshirish
if (!isLoggedIn() || isAdmin()) {
    header('Location: login');
    exit;
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Foydalanuvchi ma'lumotlarini olish
$user_stmt = $conn->prepare("SELECT id, username, hemis_id, full_name, faculty, group_name, role FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

if (!$user) {
    header('Location: login');
    exit;
}

// Parol o'zgartirish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = 'Barcha maydonlarni to\'ldiring!';
        $message_type = 'error';
    } else if ($new_password !== $confirm_password) {
        $message = 'Yangi parollar mos kelmaydi!';
        $message_type = 'error';
    } else if (strlen($new_password) < 6) {
        $message = 'Parol kamida 6 belgidan iborat bo\'lishi kerak!';
        $message_type = 'error';
    } else {
        // Joriy parolni tekshirish
        $check_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $check_stmt->execute([$user_id]);
        $user_data = $check_stmt->fetch();
        
        if ($user_data) {
            // Default parol tekshiruvi (12345678)
            $default_password = '12345678';
            $is_default = ($current_password === $default_password);
            
            // Hash qilingan parolni tekshirish
            $is_valid = password_verify($current_password, $user_data['password']);
            
            if ($is_valid || $is_default) {
                // Yangi parolni hash qilish
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Parolni yangilash
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($update_stmt->execute([$hashed_password, $user_id])) {
                    $message = 'Parol muvaffaqiyatli o\'zgartirildi!';
                    $message_type = 'success';
                } else {
                    $message = 'Xatolik yuz berdi. Iltimos, qayta urinib ko\'ring.';
                    $message_type = 'error';
                }
            } else {
                $message = 'Joriy parol noto\'g\'ri!';
                $message_type = 'error';
            }
        } else {
            $message = 'Foydalanuvchi topilmadi!';
            $message_type = 'error';
        }
    }
}

// Tilni o'rnatish (GET parametri orqali)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['uz', 'ru'])) {
    setUserLanguage($_GET['lang']);
}
$current_lang = getUserLanguage();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?php echo htmlspecialchars($user['full_name']); ?></title>
    <meta name="description" content="Foydalanuvchi profili va parol o'zgartirish">
    <?php 
    $pageTitle = $current_lang === 'ru' ? 'Профиль - ' . htmlspecialchars($user['full_name']) : 'Profil - ' . htmlspecialchars($user['full_name']);
    $pageDescription = $current_lang === 'ru' 
        ? 'Профиль пользователя. Измените пароль и просмотрите информацию.'
        : 'Foydalanuvchi profili. Parolni o\'zgartiring va ma\'lumotlarni ko\'ring.';
    echo generateMetaTags($pageTitle, $pageDescription);
    ?>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .profile-card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .profile-info {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .profile-info label {
            font-weight: bold;
            color: #666;
        }
        .profile-info span {
            color: #333;
        }
        .password-form {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Mening Profilim</h1>
            <div class="user-info">
                <a href="survey" class="btn btn-secondary">← Orqaga</a>
                <a href="../logout" class="btn btn-secondary">Chiqish</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="profile-container">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Foydalanuvchi ma'lumotlari -->
            <div class="profile-card">
                <div class="profile-header">
                    <h2>Foydalanuvchi Ma'lumotlari</h2>
                </div>
                
                <div class="profile-info">
                    <label>To'liq ism:</label>
                    <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                    
                    <label>Username:</label>
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                    
                    <?php if ($user['hemis_id']): ?>
                        <label>HEMIS ID:</label>
                        <span><?php echo htmlspecialchars($user['hemis_id']); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($user['faculty']): ?>
                        <label>Fakultet:</label>
                        <span><?php echo htmlspecialchars($user['faculty']); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($user['group_name']): ?>
                        <label>Guruh:</label>
                        <span><?php echo htmlspecialchars($user['group_name']); ?></span>
                    <?php endif; ?>
                    
                    <label>Rol:</label>
                    <span><?php echo $user['role'] === 'student' ? 'Talaba' : 'Admin'; ?></span>
                </div>
            </div>
            
            <!-- Parol o'zgartirish -->
            <div class="profile-card">
                <div class="profile-header">
                    <h2>Parolni O'zgartirish</h2>
                </div>
                
                <form method="POST" class="password-form">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label for="current_password">Joriy parol:</label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               required 
                               placeholder="Joriy parolingizni kiriting">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Yangi parol:</label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               required 
                               minlength="6"
                               placeholder="Yangi parol (kamida 6 belgi)">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Yangi parolni tasdiqlang:</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required 
                               minlength="6"
                               placeholder="Yangi parolni qayta kiriting">
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Parolni O'zgartirish</button>
                        <a href="survey" class="btn btn-secondary">Bekor qilish</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Parol mos kelishini tekshirish
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function checkPasswordMatch() {
            if (confirmPassword.value && newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Parollar mos kelmaydi!');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        newPassword.addEventListener('input', checkPasswordMatch);
        confirmPassword.addEventListener('input', checkPasswordMatch);
    </script>
</body>
</html>

