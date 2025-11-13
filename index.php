<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/base_path.php';

// Tilni o'rnatish (GET parametri orqali)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['uz', 'ru'])) {
    setUserLanguage($_GET['lang']);
}
$current_lang = getUserLanguage();

// Agar login qilgan bo'lsa, tegishli sahifaga yo'naltirish
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard');
    } else {
        header('Location: student/survey');
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
    <meta name="description" content="<?php echo $current_lang === 'ru' ? 'Анонимная система опроса для оценки преподавателей, деканов и координаторов' : 'O\'qituvchilar, dekanlar va koordinatorlarni baholash uchun anonim so\'rovnoma tizimi'; ?>">
    <?php 
    $pageTitle = $current_lang === 'ru' ? 'Анонимная система опроса' : 'Anonim So\'rovnoma';
    $pageDescription = $current_lang === 'ru' 
        ? 'Анонимная система опроса для оценки преподавателей, деканов и координаторов. Выразите свое мнение безопасно и конфиденциально.'
        : 'O\'qituvchilar, dekanlar va koordinatorlarni baholash uchun anonim so\'rovnoma tizimi. Fikringizni xavfsiz va maxfiy bildiring.';
    echo generateMetaTags($pageTitle, $pageDescription);
    ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        // Linkni nusxalash funksiyasi (Telegram cache uchun timestamp bilan)
        function copyLinkToClipboard() {
            const url = window.location.href.split('?')[0]; // Query string ni olib tashlash
            const timestamp = Date.now();
            const linkWithTimestamp = url + (url.includes('?') ? '&' : '?') + 'v=' + timestamp;
            
            // Clipboard API
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(linkWithTimestamp).then(function() {
                    alert('Link nusxalandi! Telegram\'da yuborish uchun tayyor: ' + linkWithTimestamp);
                }).catch(function(err) {
                    // Fallback: eski usul
                    fallbackCopyTextToClipboard(linkWithTimestamp);
                });
            } else {
                fallbackCopyTextToClipboard(linkWithTimestamp);
            }
        }
        
        // Fallback: eski browserlar uchun
        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                alert('Link nusxalandi! Telegram\'da yuborish uchun tayyor: ' + text);
            } catch (err) {
                console.error('Fallback: Copy qilishda xatolik', err);
                prompt('Linkni nusxalash uchun quyidagi matnni tanlang va Ctrl+C bosing:', text);
            }
            document.body.removeChild(textArea);
        }
    </script>
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
                    <a href="anonymous" class="btn btn-primary btn-large"><?php echo t('vote_button'); ?></a>
                </div>
                
                <div class="option-card">
                    <div class="option-icon">🔐</div>
                    <h2><?php echo t('login'); ?></h2>
                    <p><?php echo t('login_desc'); ?></p>
                    <a href="student/login?lang=<?php echo $current_lang; ?>" class="btn btn-secondary btn-large"><?php echo t('login_button'); ?></a>
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
                <p><?php echo t('admin_login'); ?> <a href="admin/login"><?php echo t('admin_panel'); ?></a></p>
            </div>
            
            <!-- Linkni nusxalash tugmasi (Telegram cache uchun) -->
            <div style="margin-top: 30px; text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                <p style="margin-bottom: 15px; color: #666;">
                    <strong>📱 Telegram'da ulashish uchun:</strong><br>
                    Quyidagi tugmani bosing va linkni Telegram'da yuboring
                </p>
                <button onclick="copyLinkToClipboard()" style="background: #0088cc; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 500;">
                    📋 Linkni Nusxalash (Telegram uchun)
                </button>
                <p style="margin-top: 10px; font-size: 12px; color: #999;">
                    Link avtomatik ravishda timestamp bilan nusxalanadi
                </p>
            </div>
        </div>
    </div>
</body>
</html>
