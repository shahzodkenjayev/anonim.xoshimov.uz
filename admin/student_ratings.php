<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/base_path.php';

// Admin tekshirish
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login');
    exit;
}

$student_id = intval($_GET['id'] ?? 0);

if ($student_id <= 0) {
    header('Location: add_student');
    exit;
}

$conn = getDBConnection();

// Talaba ma'lumotlari
$student_stmt = $conn->prepare("SELECT id, username, hemis_id, full_name, faculty, group_name FROM users WHERE id = ? AND role = 'student'");
$student_stmt->execute([$student_id]);
$student = $student_stmt->fetch();

if (!$student) {
    header('Location: add_student');
    exit;
}

// Talaba qaysi xodimlarga baho qo'yganini olish
$ratings_query = "SELECT 
    e.id as employee_id,
    e.full_name as employee_name,
    e.position,
    e.department_uz,
    e.department_ru,
    ss.submitted_at,
    GROUP_CONCAT(
        CONCAT(q.id, ':', COALESCE(sr.rating, ''), ':', COALESCE(sr.text_response, ''))
        SEPARATOR '|||'
    ) as responses
    FROM survey_submissions ss
    INNER JOIN employees e ON ss.employee_id = e.id
    LEFT JOIN survey_responses sr ON ss.employee_id = sr.employee_id AND ss.user_id = ?
    LEFT JOIN questions q ON sr.question_id = q.id
    WHERE ss.user_id = ?
    GROUP BY e.id, ss.submitted_at
    ORDER BY ss.submitted_at DESC";

$ratings_stmt = $conn->prepare($ratings_query);
$ratings_stmt->execute([$student_id, $student_id]);
$ratings = $ratings_stmt->fetchAll();

// Barcha savollarni olish (baho ko'rsatish uchun)
$questions_query = "SELECT id, question_text_uz, question_text_ru, question_type FROM questions ORDER BY id";
$questions_result = $conn->query($questions_query);
$questions = $questions_result->fetchAll();
$questions_map = [];
foreach ($questions as $q) {
    $questions_map[$q['id']] = $q;
}

// Umumiy statistika
$stats_query = "SELECT 
    COUNT(DISTINCT ss.employee_id) as total_rated,
    AVG(sr.rating) as avg_rating
    FROM survey_submissions ss
    LEFT JOIN survey_responses sr ON ss.employee_id = sr.employee_id AND ss.user_id = ? AND sr.rating IS NOT NULL
    WHERE ss.user_id = ?";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->execute([$student_id, $student_id]);
$stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talaba Baholari - <?php echo htmlspecialchars($student['full_name']); ?></title>
    <link rel="stylesheet" href="<?php echo css('style.css'); ?>">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Talaba Baholari</h1>
            <div class="user-info">
                <a href="add_student" class="btn btn-secondary">← Orqaga</a>
                <a href="../logout" class="btn btn-secondary">Chiqish</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- Talaba ma'lumotlari -->
        <div class="admin-section">
            <h2>Talaba Ma'lumotlari</h2>
            <div class="employee-info-card">
                <p><strong>To'liq ism:</strong> <?php echo htmlspecialchars($student['full_name']); ?></p>
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
                <div class="stats-inline">
                    <span><strong>Baholangan xodimlar:</strong> <?php echo $stats['total_rated']; ?> ta</span>
                    <?php if ($stats['avg_rating']): ?>
                        <span><strong>O'rtacha baho:</strong> <?php echo number_format($stats['avg_rating'], 2); ?> / 5.00</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
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
                <div class="filter-buttons">
                    <button type="button" id="search-btn" class="btn btn-primary">Qidirish</button>
                    <button type="button" id="reset-btn" class="btn btn-secondary">Tozalash</button>
                </div>
            </div>
            <div class="filter-results" id="filter-results"></div>
        </div>
        
        <!-- Baholangan xodimlar -->
        <div class="admin-section">
            <h2>Baholangan Xodimlar</h2>
            <?php if (count($ratings) > 0): ?>
                <div id="ratings-container">
                    <?php foreach ($ratings as $rating): ?>
                        <div class="result-card" 
                             data-name="<?php echo htmlspecialchars(strtolower($rating['employee_name'])); ?>"
                             data-position="<?php echo $rating['position']; ?>">
                            <h3><?php echo htmlspecialchars($rating['employee_name']); ?></h3>
                            <p><strong>Lavozim:</strong> <?php echo getPositionName($rating['position']); ?></p>
                            <?php if ($rating['department_uz']): ?>
                                <p><strong>Kafedra/Fakultet:</strong> <?php echo htmlspecialchars($rating['department_uz']); ?></p>
                            <?php endif; ?>
                            <p><strong>Baholangan sana:</strong> <?php echo date('d.m.Y H:i', strtotime($rating['submitted_at'])); ?></p>
                            
                            <!-- Batafsil javoblar -->
                            <?php if ($rating['responses']): ?>
                                <div class="text-responses" style="margin-top: 15px;">
                                    <?php
                                    $responses = explode('|||', $rating['responses']);
                                    $employee_responses = [];
                                    foreach ($responses as $response) {
                                        if (empty($response)) continue;
                                        $parts = explode(':', $response, 3);
                                        if (count($parts) >= 2) {
                                            $q_id = $parts[0];
                                            $rating_val = $parts[1] ?? '';
                                            $text_val = $parts[2] ?? '';
                                            if (!isset($employee_responses[$q_id])) {
                                                $employee_responses[$q_id] = ['rating' => '', 'text' => ''];
                                            }
                                            if ($rating_val) {
                                                $employee_responses[$q_id]['rating'] = $rating_val;
                                            }
                                            if ($text_val) {
                                                $employee_responses[$q_id]['text'] = $text_val;
                                            }
                                        }
                                    }
                                    
                                    foreach ($employee_responses as $q_id => $response_data):
                                        if (!isset($questions_map[$q_id])) continue;
                                        $question = $questions_map[$q_id];
                                    ?>
                                        <div class="text-response-item" style="margin-bottom: 15px;">
                                            <p><strong><?php echo htmlspecialchars($question['question_text_uz']); ?>:</strong></p>
                                            <?php if ($question['question_type'] === 'rating' && $response_data['rating']): ?>
                                                <p>
                                                    <span class="stars-small"><?php echo renderStars(intval($response_data['rating'])); ?></span>
                                                    <strong><?php echo $response_data['rating']; ?>/5</strong>
                                                </p>
                                            <?php elseif ($question['question_type'] === 'text' && $response_data['text']): ?>
                                                <p><?php echo htmlspecialchars($response_data['text']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="no-results-message" style="display: none; text-align: center; padding: 20px; color: #666;">
                    <p>Hech qanday natija topilmadi.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Bu talaba hali hech kimga baho qo'ymagan.</div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Qidirish funksiyasi
        const searchInput = document.getElementById('search-input');
        const positionFilter = document.getElementById('position-filter');
        const searchBtn = document.getElementById('search-btn');
        const resetBtn = document.getElementById('reset-btn');
        const ratingsContainer = document.getElementById('ratings-container');
        const filterResults = document.getElementById('filter-results');
        const noResultsMessage = document.getElementById('no-results-message');
        
        function filterRatings() {
            if (!ratingsContainer) return;
            
            const searchTerm = searchInput.value.toLowerCase().trim();
            const positionValue = positionFilter.value;
            
            const cards = ratingsContainer.querySelectorAll('.result-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const position = card.getAttribute('data-position') || '';
                
                const matchesSearch = !searchTerm || name.includes(searchTerm);
                const matchesPosition = !positionValue || position === positionValue;
                
                if (matchesSearch && matchesPosition) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Natijalar sonini ko'rsatish
            if (filterResults) {
                filterResults.textContent = `Topildi: ${visibleCount} ta xodim`;
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
            searchBtn.addEventListener('click', filterRatings);
        }
        
        // Tozalash tugmasi
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                searchInput.value = '';
                positionFilter.value = '';
                filterRatings();
            });
        }
        
        // Enter tugmasi bosilganda qidirish
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterRatings();
                }
            });
        }
        
        // Filter o'zgarganda avtomatik qidirish
        if (positionFilter) {
            positionFilter.addEventListener('change', filterRatings);
        }
        
        // Sahifa yuklanganda barcha natijalarni ko'rsatish
        window.addEventListener('load', function() {
            if (filterResults && ratingsContainer) {
                const totalCount = ratingsContainer.querySelectorAll('.result-card').length;
                filterResults.textContent = `Jami: ${totalCount} ta xodim`;
            }
        });
    </script>
</body>
</html>

