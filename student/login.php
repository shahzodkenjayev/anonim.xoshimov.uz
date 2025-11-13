<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Tilni o'rnatish (GET parametri orqali)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['uz', 'ru'])) {
    setUserLanguage($_GET['lang']);
}
$current_lang = getUserLanguage();

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
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $current_lang === 'ru' ? 'Студент - Вход в систему' : 'Talaba - Tizimga Kirish'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="language-selector-top">
        <div class="container">
            <div class="lang-switcher">
                <a href="?lang=uz" class="lang-option <?php echo $current_lang === 'uz' ? 'active' : ''; ?>">
                    <span class="flag-icon"><?php echo getFlagSvg('uz'); ?></span>
                    <span>O'zbek</span>
                </a>
                <a href="?lang=ru" class="lang-option <?php echo $current_lang === 'ru' ? 'active' : ''; ?>">
                    <span class="flag-icon"><?php echo getFlagSvg('ru'); ?></span>
                    <span>Русский</span>
                </a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="login-box">
            <h1><?php echo $current_lang === 'ru' ? 'Студент - Вход в систему' : 'Talaba Tizimiga Kirish'; ?></h1>
            <h2><?php echo $current_lang === 'ru' ? 'Выскажите свое мнение' : 'Fikringizni bildiring'; ?></h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username"><?php echo $current_lang === 'ru' ? 'HEMIS ID:' : 'HEMIS ID:'; ?></label>
                    <input type="text" id="username" name="username" placeholder="<?php echo $current_lang === 'ru' ? 'Введите HEMIS ID' : 'HEMIS ID ni kiriting'; ?>" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password"><?php echo $current_lang === 'ru' ? 'Пароль:' : 'Parol:'; ?></label>
                    <input type="password" id="password" name="password" placeholder="<?php echo $current_lang === 'ru' ? 'По умолчанию: 12345678' : 'Default: 12345678'; ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary"><?php echo $current_lang === 'ru' ? 'Войти' : 'Kirish'; ?></button>
            </form>
            
            <div class="login-info">
                <p><strong><?php echo $current_lang === 'ru' ? 'Примечание:' : 'Eslatma:'; ?></strong></p>
                <p><?php echo $current_lang === 'ru' ? 'Пароль по умолчанию:' : 'Default parol:'; ?> <strong>12345678</strong></p>
            </div>
            
            <div class="login-links">
                <p><a href="../?lang=<?php echo $current_lang; ?>">← <?php echo $current_lang === 'ru' ? 'Вернуться на главную' : 'Asosiy sahifaga qaytish'; ?></a></p>
                <p><a href="../anonymous?lang=<?php echo $current_lang; ?>"><?php echo $current_lang === 'ru' ? 'Анонимное голосование' : 'Anonim ovoz berish'; ?></a></p>
            </div>
        </div>
    </div>
</body>
</html>

