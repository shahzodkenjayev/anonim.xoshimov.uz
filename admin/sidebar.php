<?php
// Sidebar fayli - barcha admin sahifalarida ishlatiladi
// Eslatma: Bu fayl include qilinganda, admin tekshiruvi allaqachon o'tgan bo'lishi kerak

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
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2>Admin Panel</h2>
        <div class="user-info-sidebar">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
        </div>
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
                <a href="../logout" class="nav-link logout-link">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text">Chiqish</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

