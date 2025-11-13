<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/base_path.php';

// Admin tekshirish
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login');
    exit;
}

// Tilni o'rnatish (GET parametri orqali)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['uz', 'ru'])) {
    setUserLanguage($_GET['lang']);
}
$current_lang = getUserLanguage();

$conn = getDBConnection();

// Xodimlar ro'yxati
$employees_query = "SELECT id, full_name, position, department_uz, department_ru FROM employees ORDER BY position, full_name";
$employees_result = $conn->query($employees_query);
$employees = $employees_result->fetchAll();

// Umumiy statistika
$stats_query = "SELECT 
    COUNT(DISTINCT employees.id) as total_employees,
    COUNT(DISTINCT survey_submissions.user_id) as total_students,
    COUNT(survey_submissions.id) as total_submissions
    FROM employees
    LEFT JOIN survey_submissions ON employees.id = survey_submissions.employee_id";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Boshqaruv</title>
    <link rel="stylesheet" href="<?php echo css('style.css'); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Boshqaruv</h1>
            </div>
            
            <div class="admin-content">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Jami Xodimlar</h3>
                <p class="stat-number"><?php echo $stats['total_employees']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Jami Talabalar</h3>
                <p class="stat-number"><?php echo $stats['total_students']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Jami Topshirilgan</h3>
                <p class="stat-number"><?php echo $stats['total_submissions']; ?></p>
            </div>
        </div>
        
        <div class="admin-section">
            <h2>Boshqaruv</h2>
            <a href="employees" class="btn btn-primary">Xodimlarni boshqarish</a>
            <a href="add_student" class="btn btn-primary">Talabalarni boshqarish</a>
            <a href="create_admin" class="btn btn-primary">Admin qo'shish</a>
            <a href="results" class="btn btn-primary">Natijalarni ko'rish</a>
            <a href="questions" class="btn btn-primary">Savollarni boshqarish</a>
        </div>
        
        <div class="admin-section">
            <h2>Xodimlar</h2>
            
            <!-- Qidirish paneli -->
            <div class="filter-panel">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search-input">🔍 Xodimni qidirish:</label>
                        <input type="text" id="search-input" class="form-control" placeholder="Ism yoki familiya bo'yicha qidiring...">
                    </div>
                    <div class="filter-group">
                        <label for="position-filter">Lavozim:</label>
                        <select id="position-filter" class="form-control">
                            <option value="">Barcha lavozimlar</option>
                            <option value="teacher">O'qituvchi</option>
                            <option value="dean">Dekan</option>
                            <option value="coordinator">Koordinator</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="department-filter">Kafedra/Fakultet:</label>
                        <select id="department-filter" class="form-control">
                            <option value="">Barcha kafedralar</option>
                            <?php
                            $all_departments_query = "SELECT DISTINCT department_uz FROM employees WHERE department_uz IS NOT NULL AND department_uz != '' ORDER BY department_uz";
                            $all_departments_result = $conn->query($all_departments_query);
                            $all_departments = $all_departments_result->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($all_departments as $dept):
                            ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-buttons">
                        <button type="button" id="search-btn" class="btn btn-primary">Qidirish</button>
                        <button type="button" id="reset-btn" class="btn btn-secondary">Tozalash</button>
                    </div>
                </div>
                <div class="filter-results" id="filter-results"></div>
            </div>
            
            <?php if (count($employees) > 0): ?>
                <table class="data-table" id="employees-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ism</th>
                            <th>Lavozim</th>
                            <th>Bo'lim</th>
                            <th>Amallar</th>
                        </tr>
                    </thead>
                    <tbody id="employees-tbody">
                        <?php foreach ($employees as $employee): ?>
                            <tr data-name="<?php echo htmlspecialchars(strtolower($employee['full_name'])); ?>" 
                                data-position="<?php echo $employee['position']; ?>" 
                                data-department="<?php echo htmlspecialchars(strtolower($employee['department_uz'] ?? '')); ?>">
                                <td><?php echo $employee['id']; ?></td>
                                <td><?php echo htmlspecialchars($employee['full_name']); ?></td>
                                <td><?php echo getPositionName($employee['position']); ?></td>
                                <td><?php echo htmlspecialchars($employee['department_uz'] ?? '-'); ?></td>
                                <td>
                                    <a href="employee_results?id=<?php echo $employee['id']; ?>" class="btn btn-small">Natijalar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="no-results-message" style="display: none; text-align: center; padding: 20px; color: #666;">
                    <p>Hech qanday natija topilmadi.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Hozircha xodimlar ro'yxati bo'sh. <a href="employees">Qo'shish</a></div>
            <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        // Qidirish funksiyasi
        const searchInput = document.getElementById('search-input');
        const positionFilter = document.getElementById('position-filter');
        const departmentFilter = document.getElementById('department-filter');
        const searchBtn = document.getElementById('search-btn');
        const resetBtn = document.getElementById('reset-btn');
        const employeesTbody = document.getElementById('employees-tbody');
        const filterResults = document.getElementById('filter-results');
        const noResultsMessage = document.getElementById('no-results-message');
        
        function filterEmployees() {
            if (!employeesTbody) return;
            
            const searchTerm = searchInput.value.toLowerCase().trim();
            const positionValue = positionFilter.value;
            const departmentValue = departmentFilter.value.toLowerCase().trim();
            
            const rows = employeesTbody.querySelectorAll('tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const position = row.getAttribute('data-position') || '';
                const department = row.getAttribute('data-department') || '';
                
                const matchesSearch = !searchTerm || name.includes(searchTerm);
                const matchesPosition = !positionValue || position === positionValue;
                const matchesDepartment = !departmentValue || department.includes(departmentValue);
                
                if (matchesSearch && matchesPosition && matchesDepartment) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Natijalar sonini ko'rsatish
            if (filterResults) {
                filterResults.textContent = `Topildi: ${visibleCount} ta xodim`;
            }
            
            // Agar natija bo'lmasa, xabar ko'rsatish
            if (noResultsMessage) {
                if (visibleCount === 0 && rows.length > 0) {
                    noResultsMessage.style.display = 'block';
                } else {
                    noResultsMessage.style.display = 'none';
                }
            }
        }
        
        // Qidirish tugmasi
        if (searchBtn) {
            searchBtn.addEventListener('click', filterEmployees);
        }
        
        // Tozalash tugmasi
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                searchInput.value = '';
                positionFilter.value = '';
                departmentFilter.value = '';
                filterEmployees();
            });
        }
        
        // Enter tugmasi bosilganda qidirish
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterEmployees();
                }
            });
        }
        
        // Filter o'zgarganda avtomatik qidirish
        if (positionFilter) {
            positionFilter.addEventListener('change', filterEmployees);
        }
        
        if (departmentFilter) {
            departmentFilter.addEventListener('change', filterEmployees);
        }
        
        // Sahifa yuklanganda barcha natijalarni ko'rsatish
        window.addEventListener('load', function() {
            if (filterResults && employeesTbody) {
                const totalCount = employeesTbody.querySelectorAll('tr').length;
                filterResults.textContent = `Jami: ${totalCount} ta xodim`;
            }
        });
    </script>
</body>
</html>

