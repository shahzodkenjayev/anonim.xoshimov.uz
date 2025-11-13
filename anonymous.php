<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// Xodimlar ro'yxatini olish
$employees_query = "SELECT id, full_name, position, department FROM employees ORDER BY position, full_name";
$employees_result = $conn->query($employees_query);
$employees = $employees_result->fetchAll();

// Savollarni olish
$questions_query = "SELECT id, question_text, question_type, position_type FROM questions ORDER BY id";
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
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="survey-section">
            <h2>Xodimlarni baholash</h2>
            <p class="info-text">Quyidagi xodimlar haqida anonim so'rovnoma to'ldiring.</p>
            
            <?php if (count($employees) > 0): ?>
                <?php foreach ($employees as $employee): ?>
                    <?php 
                    $is_voted = in_array($employee['id'], $_SESSION[$session_key] ?? []);
                    ?>
                    <div class="employee-card <?php echo $is_voted ? 'submitted' : ''; ?>">
                        <div class="employee-header">
                            <h3><?php echo htmlspecialchars($employee['full_name']); ?></h3>
                            <span class="position-badge"><?php echo getPositionName($employee['position']); ?></span>
                            <?php if ($employee['department']): ?>
                                <span class="department"><?php echo htmlspecialchars($employee['department']); ?></span>
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
</body>
</html>

