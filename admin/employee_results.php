<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/base_path.php';

// Admin tekshirish
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login');
    exit;
}

$employee_id = intval($_GET['id'] ?? 0);

if ($employee_id <= 0) {
    header('Location: dashboard');
    exit;
}

$conn = getDBConnection();

// Xodim ma'lumotlari
$employee_stmt = $conn->prepare("SELECT id, full_name, position, department_uz, department_ru FROM employees WHERE id = ?");
$employee_stmt->execute([$employee_id]);
$employee = $employee_stmt->fetch();

if (!$employee) {
    header('Location: dashboard');
    exit;
}

// Umumiy statistika
$stats_query = "SELECT 
    COUNT(DISTINCT survey_submissions.user_id) as total_responses,
    AVG(sr.rating) as overall_avg
    FROM survey_submissions
    LEFT JOIN survey_responses sr ON survey_submissions.employee_id = sr.employee_id AND sr.rating IS NOT NULL
    WHERE survey_submissions.employee_id = ?";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->execute([$employee_id]);
$stats = $stats_stmt->fetch();

// Qaysi talabalar bu xodimga javob berganini olish
$students_query = "SELECT 
    u.id as user_id,
    u.username,
    u.hemis_id,
    u.full_name,
    u.faculty,
    u.group_name,
    ss.submitted_at
    FROM survey_submissions ss
    INNER JOIN users u ON ss.user_id = u.id
    WHERE ss.employee_id = ?
    ORDER BY ss.submitted_at DESC";

$students_stmt = $conn->prepare($students_query);
$students_stmt->execute([$employee_id]);
$students = $students_stmt->fetchAll();

// Har bir talaba uchun batafsil javoblarni olish
// Eslatma: survey_responses jadvalida user_id yo'q, shuning uchun vaqtni solishtirish orqali aniqlaymiz
foreach ($students as &$student) {
    // Talaba qaysi savollarga qanday javob berganini olish
    // Vaqtni solishtirish orqali talaba javoblarini aniqlash
    // Bir xodimga bir vaqtda bir nechta talaba javob bergan bo'lishi mumkin, shuning uchun vaqt oralig'ini kengaytiramiz
    $responses_query = "SELECT 
        q.id as question_id,
        q.question_text_uz,
        q.question_text_ru,
        q.question_type,
        sr.rating,
        sr.text_response,
        sr.submitted_at
        FROM survey_responses sr
        INNER JOIN questions q ON sr.question_id = q.id
        WHERE sr.employee_id = ?
        AND DATE(sr.submitted_at) = DATE(?)
        AND ABS(TIMESTAMPDIFF(SECOND, sr.submitted_at, ?)) <= 60
        ORDER BY q.id";
    
    $responses_stmt = $conn->prepare($responses_query);
    $submitted_time = $student['submitted_at'];
    $responses_stmt->execute([$employee_id, $submitted_time, $submitted_time]);
    $student['responses'] = $responses_stmt->fetchAll();
}
unset($student);

// Savollar ro'yxati (umumiy ko'rsatish uchun)
$questions_query = "SELECT 
    q.id as question_id,
    q.question_text_uz,
    q.question_text_ru,
    q.question_type,
    AVG(sr.rating) as avg_rating,
    COUNT(sr.id) as response_count,
    GROUP_CONCAT(DISTINCT sr.text_response SEPARATOR '|||') as text_responses
    FROM questions q
    LEFT JOIN survey_responses sr ON q.id = sr.question_id AND sr.employee_id = ?
    WHERE q.position_type = 'all' OR q.position_type = ?
    GROUP BY q.id
    ORDER BY q.id";

$questions_stmt = $conn->prepare($questions_query);
$questions_stmt->execute([$employee_id, $employee['position']]);
$questions = $questions_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xodim Natijalari - <?php echo htmlspecialchars($employee['full_name']); ?></title>
    <link rel="stylesheet" href="<?php echo css('style.css'); ?>">
    <style>
        .summary-section {
            margin-bottom: 30px;
        }
        .student-response-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .student-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .student-info {
            flex: 1;
        }
        .student-info p {
            margin: 5px 0;
            color: #666;
        }
        .student-info strong {
            color: #333;
        }
        .response-item {
            margin-bottom: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 6px;
            border-left: 3px solid #4a90e2;
        }
        .response-item p {
            margin: 5px 0;
        }
        .response-item strong {
            color: #4a90e2;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>Xodim Natijalari</h1>
            </div>
            
            <div class="admin-content">
        <!-- Xodim ma'lumotlari -->
        <div class="employee-info-card">
            <h2><?php echo htmlspecialchars($employee['full_name']); ?></h2>
            <p><strong>Lavozim:</strong> <?php echo getPositionName($employee['position']); ?></p>
            <?php if ($employee['department_uz']): ?>
                <p><strong>Kafedra (UZ):</strong> <?php echo htmlspecialchars($employee['department_uz']); ?></p>
            <?php endif; ?>
            <?php if ($employee['department_ru']): ?>
                <p><strong>Kafedra (RU):</strong> <?php echo htmlspecialchars($employee['department_ru']); ?></p>
            <?php endif; ?>
            <div class="stats-inline">
                <span><strong>Jami javoblar:</strong> <?php echo $stats['total_responses']; ?></span>
                <?php if ($stats['overall_avg']): ?>
                    <span><strong>O'rtacha baho:</strong> <?php echo number_format($stats['overall_avg'], 2); ?> / 5.00</span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Umumiy statistika -->
        <div class="summary-section">
            <h2>Umumiy statistika</h2>
            <?php foreach ($questions as $question): ?>
                <div class="result-card">
                    <h3><?php echo htmlspecialchars($question['question_text_uz']); ?></h3>
                    
                    <?php if ($question['question_type'] === 'rating'): ?>
                        <?php if ($question['response_count'] > 0): ?>
                            <div class="rating-result">
                                <span class="avg-rating"><?php echo number_format($question['avg_rating'], 2); ?></span>
                                <span class="stars"><?php echo renderStars(round($question['avg_rating'])); ?></span>
                                <span class="response-count">(<?php echo $question['response_count']; ?> ta javob)</span>
                            </div>
                        <?php else: ?>
                            <p class="no-data">Hozircha javoblar yo'q</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if ($question['text_responses']): ?>
                            <div class="text-responses">
                                <?php 
                                $texts = explode('|||', $question['text_responses']);
                                foreach ($texts as $text): 
                                    if (!empty(trim($text))):
                                ?>
                                    <div class="text-response-item">
                                        <p><?php echo htmlspecialchars($text); ?></p>
                                    </div>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        <?php else: ?>
                            <p class="no-data">Hozircha javoblar yo'q</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Qidirish paneli -->
        <div class="filter-panel">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="search-input">🔍 Talabani qidirish:</label>
                    <input type="text" id="search-input" class="form-control" placeholder="Ism, username yoki HEMIS ID bo'yicha qidiring...">
                </div>
                <div class="filter-group">
                    <label for="faculty-filter">Fakultet:</label>
                    <select id="faculty-filter" class="form-control">
                        <option value="">Barcha fakultetlar</option>
                        <?php
                        $faculties_query = "SELECT DISTINCT faculty FROM users WHERE faculty IS NOT NULL AND faculty != '' AND role = 'student' ORDER BY faculty";
                        $faculties_result = $conn->query($faculties_query);
                        $faculties = $faculties_result->fetchAll();
                        foreach ($faculties as $faculty):
                        ?>
                            <option value="<?php echo htmlspecialchars($faculty['faculty']); ?>"><?php echo htmlspecialchars($faculty['faculty']); ?></option>
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
        
        <!-- Talabalar javoblari -->
        <div class="results-section">
            <h2>Talabalar javoblari (<?php echo count($students); ?> ta)</h2>
            <?php if (count($students) > 0): ?>
                <div id="students-container">
                    <?php foreach ($students as $student): ?>
                        <div class="student-response-card" 
                             data-name="<?php echo htmlspecialchars(strtolower($student['full_name'])); ?>"
                             data-username="<?php echo htmlspecialchars(strtolower($student['username'])); ?>"
                             data-hemis="<?php echo htmlspecialchars(strtolower($student['hemis_id'] ?? '')); ?>"
                             data-faculty="<?php echo htmlspecialchars(strtolower($student['faculty'] ?? '')); ?>">
                            <div class="student-header">
                                <div class="student-info">
                                    <h3><?php echo htmlspecialchars($student['full_name']); ?></h3>
                                    <p><strong>Username:</strong> <?php echo htmlspecialchars($student['username']); ?></p>
                                    <?php if ($student['hemis_id']): ?>
                                        <p><strong>HEMIS ID:</strong> <?php echo htmlspecialchars($student['hemis_id']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($student['faculty']): ?>
                                        <p><strong>Fakultet:</strong> <?php echo htmlspecialchars($student['faculty']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($student['group_name']): ?>
                                        <p><strong>Guruh:</strong> <?php echo htmlspecialchars($student['group_name']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p><strong>Baholangan sana:</strong><br><?php echo date('d.m.Y H:i', strtotime($student['submitted_at'])); ?></p>
                                </div>
                            </div>
                            
                            <!-- Batafsil javoblar -->
                            <?php if (!empty($student['responses'])): ?>
                                <div class="text-responses" style="margin-top: 15px;">
                                    <?php foreach ($student['responses'] as $response): ?>
                                        <div class="response-item">
                                            <p><strong><?php echo htmlspecialchars($response['question_text_uz']); ?>:</strong></p>
                                            <?php if ($response['question_type'] === 'rating' && $response['rating']): ?>
                                                <p>
                                                    <span class="stars-small"><?php echo renderStars(intval($response['rating'])); ?></span>
                                                    <strong style="color: #4a90e2; margin-left: 10px;"><?php echo $response['rating']; ?>/5</strong>
                                                </p>
                                            <?php elseif ($response['question_type'] === 'text' && $response['text_response']): ?>
                                                <p style="color: #333; margin-top: 5px;"><?php echo nl2br(htmlspecialchars($response['text_response'])); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">Javoblar topilmadi.</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="no-results-message" style="display: none; text-align: center; padding: 20px; color: #666;">
                    <p>Hech qanday natija topilmadi.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Hozircha hech kim bu xodimga javob bermagan.</div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Qidirish funksiyasi
        const searchInput = document.getElementById('search-input');
        const facultyFilter = document.getElementById('faculty-filter');
        const searchBtn = document.getElementById('search-btn');
        const resetBtn = document.getElementById('reset-btn');
        const studentsContainer = document.getElementById('students-container');
        const filterResults = document.getElementById('filter-results');
        const noResultsMessage = document.getElementById('no-results-message');
        
        function filterStudents() {
            if (!studentsContainer) return;
            
            const searchTerm = searchInput.value.toLowerCase().trim();
            const facultyValue = facultyFilter.value.toLowerCase();
            
            const cards = studentsContainer.querySelectorAll('.student-response-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const username = card.getAttribute('data-username') || '';
                const hemis = card.getAttribute('data-hemis') || '';
                const faculty = card.getAttribute('data-faculty') || '';
                
                const matchesSearch = !searchTerm || 
                    name.includes(searchTerm) || 
                    username.includes(searchTerm) || 
                    hemis.includes(searchTerm);
                const matchesFaculty = !facultyValue || faculty === facultyValue;
                
                if (matchesSearch && matchesFaculty) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Natijalar sonini ko'rsatish
            if (filterResults) {
                filterResults.textContent = `Topildi: ${visibleCount} ta talaba`;
            }
            
            // Agar natija bo'lmasa, xabar ko'rsatish
            if (noResultsMessage) {
                if (visibleCount === 0 && cards.length > 0) {
                    noResultsMessage.style.display = 'block';
                } else {
                    noResultsMessage.style.display = 'none';
                }
            }
        }
        
        // Qidirish tugmasi
        if (searchBtn) {
            searchBtn.addEventListener('click', filterStudents);
        }
        
        // Tozalash tugmasi
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                searchInput.value = '';
                facultyFilter.value = '';
                filterStudents();
            });
        }
        
        // Enter tugmasi bosilganda qidirish
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterStudents();
                }
            });
        }
        
        // Filter o'zgarganda avtomatik qidirish
        if (facultyFilter) {
            facultyFilter.addEventListener('change', filterStudents);
        }
        
        // Sahifa yuklanganda barcha natijalarni ko'rsatish
        window.addEventListener('load', function() {
            if (filterResults && studentsContainer) {
                const totalCount = studentsContainer.querySelectorAll('.student-response-card').length;
                filterResults.textContent = `Jami: ${totalCount} ta talaba`;
            }
        });
    </script>
            </div>
        </main>
    </div>
</body>
</html>
