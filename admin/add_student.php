<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/base_path.php';

// Admin tekshirish
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login');
    exit;
}

$conn = getDBConnection();
$message = '';
$message_type = '';

// Talaba qo'shish
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    
    if (!empty($username) && !empty($password) && !empty($full_name)) {
        // Username mavjudligini tekshirish
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->execute([$username]);
        $check_result = $check_stmt->fetch();
        
        if ($check_result) {
            $message = 'Bu foydalanuvchi nomi allaqachon mavjud!';
            $message_type = 'error';
        } else {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, 'student')");
                $stmt->execute([$username, $hashed_password, $full_name]);
                $message = 'Talaba muvaffaqiyatli qo\'shildi!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Xatolik yuz berdi!';
                $message_type = 'error';
            }
        }
    } else {
        $message = 'Barcha maydonlarni to\'ldiring!';
        $message_type = 'error';
    }
}

// Talabalar ro'yxati (baholangan xodimlar soni bilan)
$students_query = "SELECT 
    u.id, 
    u.username, 
    u.full_name, 
    u.hemis_id,
    u.faculty,
    u.group_name,
    u.created_at,
    COUNT(DISTINCT ss.employee_id) as rated_employees_count
    FROM users u
    LEFT JOIN survey_submissions ss ON u.id = ss.user_id
    WHERE u.role = 'student'
    GROUP BY u.id
    ORDER BY u.created_at DESC";
$students_result = $conn->query($students_query);
$students = $students_result->fetchAll();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talabalarni Boshqarish</title>
    <link rel="stylesheet" href="<?php echo css('style.css'); ?>">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Talabalarni Boshqarish</h1>
            <div class="user-info">
                <a href="dashboard" class="btn btn-secondary">Orqaga</a>
                <a href="../logout" class="btn btn-secondary">Chiqish</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="admin-section">
            <h2>Yangi talaba qo'shish</h2>
            <form method="POST" class="form-inline">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Foydalanuvchi nomi" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Parol" required>
                </div>
                <div class="form-group">
                    <input type="text" name="full_name" placeholder="To'liq ism" required>
                </div>
                <button type="submit" class="btn btn-primary">Qo'shish</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>Talabalar ro'yxati</h2>
            
            <!-- Qidirish paneli -->
            <div class="filter-panel">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search-input">🔍 Talabani qidirish:</label>
                        <input type="text" id="search-input" class="form-control" placeholder="Ism, familiya, username yoki HEMIS ID bo'yicha qidiring...">
                    </div>
                    <div class="filter-group">
                        <label for="faculty-filter">Fakultet:</label>
                        <select id="faculty-filter" class="form-control">
                            <option value="">Barcha fakultetlar</option>
                            <?php
                            $faculties_query = "SELECT DISTINCT faculty FROM users WHERE role = 'student' AND faculty IS NOT NULL AND faculty != '' ORDER BY faculty";
                            $faculties_result = $conn->query($faculties_query);
                            $faculties = $faculties_result->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($faculties as $faculty):
                            ?>
                                <option value="<?php echo htmlspecialchars($faculty); ?>"><?php echo htmlspecialchars($faculty); ?></option>
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
            
            <?php if (count($students) > 0): ?>
                <table class="data-table" id="students-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Foydalanuvchi nomi</th>
                            <th>To'liq ism</th>
                            <th>HEMIS ID</th>
                            <th>Fakultet</th>
                            <th>Guruh</th>
                            <th>Baholangan xodimlar</th>
                            <th>Qo'shilgan sana</th>
                            <th>Amallar</th>
                        </tr>
                    </thead>
                    <tbody id="students-tbody">
                        <?php foreach ($students as $student): ?>
                            <tr data-name="<?php echo htmlspecialchars(strtolower($student['full_name'] . ' ' . ($student['username'] ?? '') . ' ' . ($student['hemis_id'] ?? ''))); ?>" 
                                data-faculty="<?php echo htmlspecialchars(strtolower($student['faculty'] ?? '')); ?>">
                                <td><?php echo $student['id']; ?></td>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['hemis_id'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($student['faculty'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($student['group_name'] ?? '-'); ?></td>
                                <td>
                                    <strong style="color: #4a90e2;"><?php echo $student['rated_employees_count']; ?></strong> ta
                                    <?php if ($student['rated_employees_count'] > 0): ?>
                                        <a href="student_ratings?id=<?php echo $student['id']; ?>" class="btn btn-small" style="margin-left: 10px;">Batafsil</a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($student['created_at'])); ?></td>
                                <td>
                                    <a href="student_ratings?id=<?php echo $student['id']; ?>" class="btn btn-small">Batafsil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="no-results-message" style="display: none; text-align: center; padding: 20px; color: #666;">
                    <p>Hech qanday natija topilmadi.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Hozircha talabalar ro'yxati bo'sh.</div>
            <?php endif; ?>
        </div>
        
        <script>
            // Qidirish funksiyasi
            const searchInput = document.getElementById('search-input');
            const facultyFilter = document.getElementById('faculty-filter');
            const searchBtn = document.getElementById('search-btn');
            const resetBtn = document.getElementById('reset-btn');
            const studentsTbody = document.getElementById('students-tbody');
            const filterResults = document.getElementById('filter-results');
            const noResultsMessage = document.getElementById('no-results-message');
            
            function filterStudents() {
                if (!studentsTbody) return;
                
                const searchTerm = searchInput.value.toLowerCase().trim();
                const facultyValue = facultyFilter.value.toLowerCase().trim();
                
                const rows = studentsTbody.querySelectorAll('tr');
                let visibleCount = 0;
                
                rows.forEach(row => {
                    const name = row.getAttribute('data-name') || '';
                    const faculty = row.getAttribute('data-faculty') || '';
                    
                    const matchesSearch = !searchTerm || name.includes(searchTerm);
                    const matchesFaculty = !facultyValue || faculty.includes(facultyValue);
                    
                    if (matchesSearch && matchesFaculty) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Natijalar sonini ko'rsatish
                if (filterResults) {
                    filterResults.textContent = `Topildi: ${visibleCount} ta talaba`;
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
                if (filterResults && studentsTbody) {
                    const totalCount = studentsTbody.querySelectorAll('tr').length;
                    filterResults.textContent = `Jami: ${totalCount} ta talaba`;
                }
            });
        </script>
    </div>
</body>
</html>

