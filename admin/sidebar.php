<?php
// Sidebar fayli - barcha admin sahifalarida ishlatiladi
// Eslatma: Bu fayl include qilinganda, admin tekshiruvi allaqachon o'tgan bo'lishi kerak

// Tilni o'rnatish (GET parametri orqali)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['uz', 'ru'])) {
    setUserLanguage($_GET['lang']);
}
$current_lang = getUserLanguage();

// Joriy sahifani aniqlash (active link uchun)
$current_page = basename($_SERVER['PHP_SELF'], '.php');
if (empty($current_page) || $current_page === 'index') {
    $current_page = 'dashboard';
}

// employee_results va student_ratings uchun alohida tekshirish
if ($current_page === 'employee_results') {
    $current_page = 'results';
} elseif ($current_page === 'student_ratings') {
    $current_page = 'add_student';
}

// Joriy URL ni olish (til parametrini qo'shish uchun)
$current_url = $_SERVER['REQUEST_URI'];
$url_parts = parse_url($current_url);
$query_params = [];
if (isset($url_parts['query'])) {
    parse_str($url_parts['query'], $query_params);
}
$query_params['lang'] = 'uz'; // Default
$url_uz = $url_parts['path'] . '?' . http_build_query($query_params);
$query_params['lang'] = 'ru';
$url_ru = $url_parts['path'] . '?' . http_build_query($query_params);
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2>Admin Panel</h2>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="dashboard" class="nav-link <?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Boshqaruv</span>
                </a>
            </li>
            <li>
                <a href="employees" class="nav-link <?php echo ($current_page === 'employees') ? 'active' : ''; ?>">
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">Xodimlar</span>
                </a>
            </li>
            <li>
                <a href="add_student" class="nav-link <?php echo ($current_page === 'add_student') ? 'active' : ''; ?>">
                    <span class="nav-icon">🎓</span>
                    <span class="nav-text">Talabalar</span>
                </a>
            </li>
            <li>
                <a href="results" class="nav-link <?php echo ($current_page === 'results') ? 'active' : ''; ?>">
                    <span class="nav-icon">📈</span>
                    <span class="nav-text">Natijalar</span>
                </a>
            </li>
            <li>
                <a href="questions" class="nav-link <?php echo ($current_page === 'questions') ? 'active' : ''; ?>">
                    <span class="nav-icon">❓</span>
                    <span class="nav-text">Savollar</span>
                </a>
            </li>
            <li>
                <a href="create_admin" class="nav-link <?php echo ($current_page === 'create_admin') ? 'active' : ''; ?>">
                    <span class="nav-icon">👤</span>
                    <span class="nav-text">Admin qo'shish</span>
                </a>
            </li>
            <li class="sidebar-divider"></li>
            <li>
                <a href="settings" class="nav-link <?php echo ($current_page === 'settings') ? 'active' : ''; ?>">
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-text">Sozlamalar</span>
                </a>
            </li>
            <li>
                <a href="../logout" class="nav-link logout-link">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text">Chiqish</span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <!-- Til tanlash -->
        <div class="sidebar-lang-switcher">
            <a href="<?php echo htmlspecialchars($url_uz); ?>" class="lang-option-sidebar <?php echo $current_lang === 'uz' ? 'active' : ''; ?>">
                <span class="flag-icon-small"><?php echo getFlagSvg('uz'); ?></span>
                <span>O'zbek</span>
            </a>
            <a href="<?php echo htmlspecialchars($url_ru); ?>" class="lang-option-sidebar <?php echo $current_lang === 'ru' ? 'active' : ''; ?>">
                <span class="flag-icon-small"><?php echo getFlagSvg('ru'); ?></span>
                <span>Русский</span>
            </a>
        </div>
        <!-- Foydalanuvchi ma'lumotlari -->
        <div class="user-info-sidebar">
            <span class="user-username-only">@<?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
    </div>
</aside>

