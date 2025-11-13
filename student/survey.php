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

// Savollarni olish
$questions_query = "SELECT id, question_text, question_type, position_type FROM questions ORDER BY id";
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
            $message = 'Siz bu xodimga allaqachon javob bergansiz!';
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
                $message = 'So\'rovnoma muvaffaqiyatli topshirildi! Rahmat!';
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
        
        <div class="survey-section">
            <h2>Xodimlarni baholash</h2>
            <p class="info-text">Quyidagi xodimlar haqida anonim so'rovnoma to'ldiring. Har bir xodimga faqat bir marta javob bera olasiz.</p>
            
            <?php if (count($employees) > 0): ?>
                <?php foreach ($employees as $employee): ?>
                    <?php 
                    $is_submitted = in_array($employee['id'], $submitted_employees);
                    ?>
                    <div class="employee-card <?php echo $is_submitted ? 'submitted' : ''; ?>">
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
                                            <?php echo htmlspecialchars($question['question_text']); ?>
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
                                
                                <button type="submit" class="btn btn-primary">Topshirish</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">Hozircha xodimlar ro'yxati bo'sh.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

