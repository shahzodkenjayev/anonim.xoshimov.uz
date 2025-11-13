<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Login tekshirish
if (!isLoggedIn() || isAdmin()) {
    header('Location: login.php');
    exit;
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Xodimlar ro'yxatini olish
$employees_query = "SELECT id, full_name, position, department_uz, department_ru FROM employees ORDER BY position, full_name";
$employees_result = $conn->query($employees_query);
$employees = $employees_result->fetchAll();

// Barcha kafedralarni olish (filtrlash uchun)
$departments_query = "SELECT DISTINCT department_uz FROM employees WHERE department_uz IS NOT NULL AND department_uz != '' ORDER BY department_uz";
$departments_result = $conn->query($departments_query);
$departments = $departments_result->fetchAll(PDO::FETCH_COLUMN);

// Tilni o'rnatish (GET parametri orqali)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['uz', 'ru'])) {
    setUserLanguage($_GET['lang']);
}
$current_lang = getUserLanguage();

// Savollarni olish (ikki tilni qo'llab-quvvatlash)
$questions_query = "SELECT id, question_text, question_text_uz, question_text_ru, question_type, position_type FROM questions ORDER BY position_type, id";
$questions_result = $conn->query($questions_query);
$questions = $questions_result->fetchAll();

// Forma yuborilganda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = intval($_POST['employee_id'] ?? 0);
    
    if ($employee_id > 0) {
        // Bu talaba bu xodimga allaqachon javob berganmi?
        $check_stmt = $conn->prepare("SELECT id FROM survey_submissions WHERE user_id = ? AND employee_id = ?");
        $check_stmt->execute([$user_id, $employee_id]);
        $check_result = $check_stmt->fetch();
        
        if ($check_result) {
            $message = t('already_voted');
            $message_type = 'error';
        } else {
            // Javoblarni saqlash
            $conn->beginTransaction();
            try {
                foreach ($questions as $question) {
                    $question_id = $question['id'];
                    $rating = null;
                    $text_response = null;
                    
                    if ($question['question_type'] === 'rating') {
                        $rating = intval($_POST['question_' . $question_id] ?? 0);
                        if ($rating < 1 || $rating > 5) continue;
                    } else {
                        $text_response = sanitize($_POST['question_' . $question_id] ?? '');
                    }
                    
                    $insert_stmt = $conn->prepare("INSERT INTO survey_responses (employee_id, question_id, rating, text_response) VALUES (?, ?, ?, ?)");
                    $insert_stmt->execute([$employee_id, $question_id, $rating, $text_response]);
                }
                
                // Topshirilganligini belgilash
                $submission_stmt = $conn->prepare("INSERT INTO survey_submissions (user_id, employee_id) VALUES (?, ?)");
                $submission_stmt->execute([$user_id, $employee_id]);
                
                $conn->commit();
                $message = t('vote_success');
                $message_type = 'success';
            } catch (Exception $e) {
                $conn->rollBack();
                $message = 'Xatolik yuz berdi: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Talaba tomonidan javob berilgan xodimlar ro'yxati
$submitted_query = "SELECT employee_id FROM survey_submissions WHERE user_id = ?";
$submitted_stmt = $conn->prepare($submitted_query);
$submitted_stmt->execute([$user_id]);
$submitted_result = $submitted_stmt->fetchAll(PDO::FETCH_COLUMN);
$submitted_employees = $submitted_result;
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>So'rovnoma - Talaba</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Anonim So'rovnoma</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="../logout.php" class="btn btn-secondary">Chiqish</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="language-selector-top">
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
        
        <div class="survey-section">
            <h2><?php echo t('rate_employees'); ?></h2>
            <p class="info-text"><?php echo t('rate_employees_desc'); ?></p>
            
            <!-- Qidirish va filtrlash paneli -->
            <div class="filter-panel">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search-input">🔍 <?php echo t('search_employee'); ?></label>
                        <input type="text" id="search-input" class="form-control" placeholder="<?php echo t('search_placeholder'); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="position-filter">📋 <?php echo t('position'); ?></label>
                        <select id="position-filter" class="form-control">
                            <option value=""><?php echo t('all_positions'); ?></option>
                            <option value="teacher"><?php echo t('teacher'); ?></option>
                            <option value="dean"><?php echo t('dean'); ?></option>
                            <option value="coordinator"><?php echo t('coordinator'); ?></option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="department-filter">🏛️ <?php echo t('department'); ?></label>
                        <select id="department-filter" class="form-control">
                            <option value=""><?php echo t('all_departments'); ?></option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group filter-buttons">
                        <button type="button" id="search-btn" class="btn btn-primary"><?php echo t('search'); ?></button>
                        <button type="button" id="clear-filters" class="btn btn-secondary"><?php echo t('clear'); ?></button>
                    </div>
                </div>
                <div class="filter-results">
                    <span id="results-count"><?php echo count($employees); ?> <?php echo t('employees_found'); ?></span>
                </div>
            </div>
            
            <?php if (count($employees) > 0): ?>
                <?php foreach ($employees as $employee): ?>
                    <?php 
                    $is_submitted = in_array($employee['id'], $submitted_employees);
                    ?>
                    <div class="employee-card <?php echo $is_submitted ? 'submitted' : ''; ?>" 
                         data-name="<?php echo htmlspecialchars(strtolower($employee['full_name'])); ?>"
                         data-position="<?php echo htmlspecialchars($employee['position']); ?>"
                         data-department="<?php echo htmlspecialchars($employee['department_uz'] ?? ''); ?>">
                        <div class="employee-header">
                            <h3><?php echo htmlspecialchars($employee['full_name']); ?></h3>
                            <span class="position-badge"><?php echo getPositionName($employee['position']); ?></span>
                            <?php if ($employee['department_uz']): ?>
                                <span class="department"><?php echo htmlspecialchars($employee['department_uz']); ?></span>
                            <?php endif; ?>
                            <?php if ($is_submitted): ?>
                                <span class="submitted-badge">✓ Topshirilgan</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!$is_submitted): ?>
                            <form method="POST" class="survey-form">
                                <input type="hidden" name="employee_id" value="<?php echo $employee['id']; ?>">
                                
                                <?php foreach ($questions as $question): ?>
                                    <?php 
                                    // Savol bu xodim pozitsiyasiga tegishlimi?
                                    if ($question['position_type'] !== 'all' && $question['position_type'] !== $employee['position']) {
                                        continue;
                                    }
                                    ?>
                                    <div class="question-group">
                                        <label class="question-label">
                                            <?php echo htmlspecialchars(getQuestionText($question, $current_lang)); ?>
                                        </label>
                                        
                                        <?php if ($question['question_type'] === 'rating'): ?>
                                            <div class="rating-group">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <label class="rating-option">
                                                        <input type="radio" name="question_<?php echo $question['id']; ?>" value="<?php echo $i; ?>" required>
                                                        <span class="star"><?php echo $i; ?> ★</span>
                                                    </label>
                                                <?php endfor; ?>
                                            </div>
                                        <?php else: ?>
                                            <textarea name="question_<?php echo $question['id']; ?>" rows="3" class="form-control" placeholder="Javobingizni yozing..."></textarea>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                
                                <button type="submit" class="btn btn-primary"><?php echo t('submit'); ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info"><?php echo t('no_employees'); ?></div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Qidirish va filtrlash funksiyalari
        const searchInput = document.getElementById('search-input');
        const positionFilter = document.getElementById('position-filter');
        const departmentFilter = document.getElementById('department-filter');
        const clearFiltersBtn = document.getElementById('clear-filters');
        const searchBtn = document.getElementById('search-btn');
        const resultsCount = document.getElementById('results-count');
        const employeeCards = document.querySelectorAll('.employee-card');
        
        function filterEmployees() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const selectedPosition = positionFilter.value;
            const selectedDepartment = departmentFilter.value;
            
            let visibleCount = 0;
            
            employeeCards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const position = card.getAttribute('data-position') || '';
                const department = card.getAttribute('data-department') || '';
                
                // Qidirish tekshiruvi
                const matchesSearch = !searchTerm || name.includes(searchTerm);
                
                // Lavozim tekshiruvi
                const matchesPosition = !selectedPosition || position === selectedPosition;
                
                // Kafedra tekshiruvi
                const matchesDepartment = !selectedDepartment || department === selectedDepartment;
                
                // Barcha shartlar bajarilsa, ko'rsatish
                if (matchesSearch && matchesPosition && matchesDepartment) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Natijalar sonini yangilash
            const lang = '<?php echo $current_lang; ?>';
            const foundText = lang === 'ru' ? 'сотрудников найдено' : 'ta xodim topildi';
            resultsCount.textContent = visibleCount + ' ' + foundText;
            
            // Agar hech narsa topilmasa, xabar ko'rsatish
            if (visibleCount === 0) {
                const noResults = document.getElementById('no-results-message');
                if (!noResults) {
                    const message = document.createElement('div');
                    message.id = 'no-results-message';
                    message.className = 'alert alert-info';
                    message.textContent = lang === 'ru' ? 'Сотрудники не найдены. Измените фильтры.' : 'Hech qanday xodim topilmadi. Filtrlarni o\'zgartiring.';
                    document.querySelector('.survey-section').appendChild(message);
                }
            } else {
                const noResults = document.getElementById('no-results-message');
                if (noResults) {
                    noResults.remove();
                }
            }
        }
        
        // Qidirish tugmasi
        searchBtn.addEventListener('click', filterEmployees);
        
        // Enter tugmasi bosilganda qidirish
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterEmployees();
            }
        });
        
        // Tozalash tugmasi
        clearFiltersBtn.addEventListener('click', function() {
            searchInput.value = '';
            positionFilter.value = '';
            departmentFilter.value = '';
            filterEmployees();
        });
    </script>
</body>
</html>

