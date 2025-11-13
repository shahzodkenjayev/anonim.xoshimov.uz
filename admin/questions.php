<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin tekshirish
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../index.php');
    exit;
}

$conn = getDBConnection();
$message = '';
$message_type = '';

// Savol qo'shish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $question_text = sanitize($_POST['question_text'] ?? '');
    $question_type = sanitize($_POST['question_type'] ?? 'rating');
    $position_type = sanitize($_POST['position_type'] ?? 'all');
    
    if (!empty($question_text)) {
        try {
            $stmt = $conn->prepare("INSERT INTO questions (question_text, question_type, position_type) VALUES (?, ?, ?)");
            $stmt->execute([$question_text, $question_type, $position_type]);
            $message = 'Savol muvaffaqiyatli qo\'shildi!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Xatolik yuz berdi!';
            $message_type = 'error';
        }
    }
}

// Savol o'chirish
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Savol o\'chirildi!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Xatolik yuz berdi!';
        $message_type = 'error';
    }
}

// Savollar ro'yxati
$questions_query = "SELECT id, question_text, question_type, position_type FROM questions ORDER BY id";
$questions_result = $conn->query($questions_query);
$questions = $questions_result->fetchAll();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savollarni Boshqarish</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Savollarni Boshqarish</h1>
            <div class="user-info">
                <a href="dashboard.php" class="btn btn-secondary">Orqaga</a>
                <a href="../logout.php" class="btn btn-secondary">Chiqish</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="admin-section">
            <h2>Yangi savol qo'shish</h2>
            <form method="POST" class="form-inline">
                <input type="hidden" name="action" value="add">
                <div class="form-group full-width">
                    <textarea name="question_text" placeholder="Savol matni" rows="2" required></textarea>
                </div>
                <div class="form-group">
                    <select name="question_type" required>
                        <option value="rating">Baholash (1-5)</option>
                        <option value="text">Matnli javob</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="position_type" required>
                        <option value="all">Barcha xodimlar</option>
                        <option value="teacher">Faqat o'qituvchilar</option>
                        <option value="dean">Faqat dekanlar</option>
                        <option value="coordinator">Faqat koordinatorlar</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Qo'shish</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>Savollar ro'yxati</h2>
            <?php if (count($questions) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Savol</th>
                            <th>Turi</th>
                            <th>Lavozim</th>
                            <th>Amallar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $question): ?>
                            <tr>
                                <td><?php echo $question['id']; ?></td>
                                <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                <td><?php echo $question['question_type'] === 'rating' ? 'Baholash' : 'Matnli'; ?></td>
                                <td>
                                    <?php 
                                    if ($question['position_type'] === 'all') {
                                        echo 'Barcha';
                                    } else {
                                        echo getPositionName($question['position_type']);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="?delete=<?php echo $question['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Rostdan o\'chirmoqchimisiz?')">O'chirish</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">Hozircha savollar ro'yxati bo'sh.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

