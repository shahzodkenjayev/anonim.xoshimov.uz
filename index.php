<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Tilni o'rnatish (GET parametri orqali)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['uz', 'ru'])) {
    setUserLanguage($_GET['lang']);
}
$current_lang = getUserLanguage();

// Agar login qilgan bo'lsa, tegishli sahifaga yo'naltirish
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: student/survey.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('site_title'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
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
        <div class="home-page">
            <div class="hero-section">
                <h1><?php echo t('site_title'); ?></h1>
                <p class="subtitle"><?php echo t('site_subtitle'); ?></p>
            </div>
            
            <div class="options-grid">
                <div class="option-card">
                    <div class="option-icon">🗳️</div>
                    <h2><?php echo t('anonymous_vote'); ?></h2>
                    <p><?php echo t('anonymous_vote_desc'); ?></p>
                    <a href="anonymous.php" class="btn btn-primary btn-large"><?php echo t('vote_button'); ?></a>
                </div>
                
                <div class="option-card">
                    <div class="option-icon">🔐</div>
                    <h2><?php echo t('login'); ?></h2>
                    <p><?php echo t('login_desc'); ?></p>
                    <a href="student/login.php" class="btn btn-secondary btn-large"><?php echo t('login_button'); ?></a>
                </div>
            </div>
            
            <div class="info-section">
                <h3><?php echo t('how_it_works'); ?></h3>
                <div class="info-grid">
                    <div class="info-item">
                        <strong><?php echo t('step1_title'); ?></strong>
                        <p><?php echo t('step1_desc'); ?></p>
                    </div>
                    <div class="info-item">
                        <strong><?php echo t('step2_title'); ?></strong>
                        <p><?php echo t('step2_desc'); ?></p>
                    </div>
                    <div class="info-item">
                        <strong><?php echo t('step3_title'); ?></strong>
                        <p><?php echo t('step3_desc'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="login-links">
                <p><?php echo t('admin_login'); ?> <a href="admin/login.php"><?php echo t('admin_panel'); ?></a></p>
            </div>
        </div>
    </div>
</body>
</html>
