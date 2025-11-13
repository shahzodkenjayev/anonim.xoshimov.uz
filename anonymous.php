<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$conn = getDBConnection();
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

// Anonim ovoz berish uchun session yoki cookie ishlatamiz
$session_key = 'anonymous_votes';
if (!isset($_SESSION[$session_key])) {
    $_SESSION[$session_key] = [];
}

// Forma yuborilganda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = intval($_POST['employee_id'] ?? 0);
    
    if ($employee_id > 0) {
        // Bu browser'dan bu xodimga allaqachon ovoz berilganmi?
        if (in_array($employee_id, $_SESSION[$session_key])) {
            $message = 'Siz bu xodimga allaqachon ovoz bergansiz!';
            $message_type = 'error';
        } else {
            // Javoblarni saqlash (anonim, user_id = NULL)
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
                    
                    // Anonim javob - user_id NULL
                    $insert_stmt = $conn->prepare("INSERT INTO survey_responses (employee_id, question_id, rating, text_response) VALUES (?, ?, ?, ?)");
                    $insert_stmt->execute([$employee_id, $question_id, $rating, $text_response]);
                }
                
                // Session'da saqlash (bir marta ovoz berishni ta'minlash uchun)
                $_SESSION[$session_key][] = $employee_id;
                
                $conn->commit();
                $message = 'Ovozingiz muvaffaqiyatli qabul qilindi! Rahmat!';
                $message_type = 'success';
            } catch (Exception $e) {
                $conn->rollBack();
                $message = 'Xatolik yuz berdi: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anonim Ovoz Berish</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Anonim Ovoz Berish</h1>
            <div class="user-info">
                <a href="index.php" class="btn btn-secondary">← Asosiy sahifa</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="anonymous-info">
            <div class="alert alert-info">
                <strong>ℹ️ Anonimlik:</strong> Sizning ovozingiz to'liq anonim. Login qilish shart emas. 
                Har bir xodimga faqat bir marta ovoz bera olasiz.
            </div>
            <div class="language-selector" style="text-align: right; margin-top: 10px;">
                <label for="lang-select">Til / Язык:</label>
                <select id="lang-select" onchange="window.location.href='?lang=' + this.value" style="padding: 5px 10px; margin-left: 10px; border-radius: 5px;">
                    <option value="uz" <?php echo $current_lang === 'uz' ? 'selected' : ''; ?>>O'zbek</option>
                    <option value="ru" <?php echo $current_lang === 'ru' ? 'selected' : ''; ?>>Русский</option>
                </select>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="survey-section">
            <h2>Xodimlarni baholash</h2>
            <p class="info-text">Quyidagi xodimlar haqida anonim so'rovnoma to'ldiring.</p>
            
            <!-- Qidirish va filtrlash paneli -->
            <div class="filter-panel">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search-input">🔍 Xodimni qidirish:</label>
                        <input type="text" id="search-input" class="form-control" placeholder="Ism yoki familiya bo'yicha qidiring...">
                    </div>
                    <div class="filter-group">
                        <label for="position-filter">📋 Lavozim:</label>
                        <select id="position-filter" class="form-control">
                            <option value="">Barcha lavozimlar</option>
                            <option value="teacher">O'qituvchi</option>
                            <option value="dean">Dekan</option>
                            <option value="coordinator">Koordinator</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="department-filter">🏛️ Kafedra:</label>
                        <select id="department-filter" class="form-control">
                            <option value="">Barcha kafedralar</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="button" id="clear-filters" class="btn btn-secondary">Tozalash</button>
                    </div>
                </div>
                <div class="filter-results">
                    <span id="results-count"><?php echo count($employees); ?> ta xodim topildi</span>
                </div>
            </div>
            
            <?php if (count($employees) > 0): ?>
                <?php foreach ($employees as $employee): ?>
                    <?php 
                    $is_voted = in_array($employee['id'], $_SESSION[$session_key] ?? []);
                    ?>
                    <div class="employee-card <?php echo $is_voted ? 'submitted' : ''; ?>" 
                         data-name="<?php echo htmlspecialchars(strtolower($employee['full_name'])); ?>"
                         data-position="<?php echo htmlspecialchars($employee['position']); ?>"
                         data-department="<?php echo htmlspecialchars($employee['department_uz'] ?? ''); ?>">
                        <div class="employee-header">
                            <h3><?php echo htmlspecialchars($employee['full_name']); ?></h3>
                            <span class="position-badge"><?php echo getPositionName($employee['position']); ?></span>
                            <?php if ($employee['department_uz']): ?>
                                <span class="department"><?php echo htmlspecialchars($employee['department_uz']); ?></span>
                            <?php endif; ?>
                            <?php if ($is_voted): ?>
                                <span class="submitted-badge">✓ Ovoz berilgan</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!$is_voted): ?>
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
                                
                                <button type="submit" class="btn btn-primary">Ovoz Berish</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">Hozircha xodimlar ro'yxati bo'sh.</div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Qidirish va filtrlash funksiyalari
        const searchInput = document.getElementById('search-input');
        const positionFilter = document.getElementById('position-filter');
        const departmentFilter = document.getElementById('department-filter');
        const clearFiltersBtn = document.getElementById('clear-filters');
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
            resultsCount.textContent = visibleCount + ' ta xodim topildi';
            
            // Agar hech narsa topilmasa, xabar ko'rsatish
            if (visibleCount === 0) {
                const noResults = document.getElementById('no-results-message');
                if (!noResults) {
                    const message = document.createElement('div');
                    message.id = 'no-results-message';
                    message.className = 'alert alert-info';
                    message.textContent = 'Hech qanday xodim topilmadi. Filtrlarni o\'zgartiring.';
                    document.querySelector('.survey-section').appendChild(message);
                }
            } else {
                const noResults = document.getElementById('no-results-message');
                if (noResults) {
                    noResults.remove();
                }
            }
        }
        
        // Event listener'lar
        searchInput.addEventListener('input', filterEmployees);
        positionFilter.addEventListener('change', filterEmployees);
        departmentFilter.addEventListener('change', filterEmployees);
        
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

