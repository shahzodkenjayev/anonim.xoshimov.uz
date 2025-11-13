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

// Xodim qo'shish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $position = sanitize($_POST['position'] ?? '');
    $department_uz = sanitize($_POST['department_uz'] ?? '');
    $department_ru = sanitize($_POST['department_ru'] ?? '');
    
    if (!empty($full_name) && !empty($position)) {
        try {
            $stmt = $conn->prepare("INSERT INTO employees (full_name, position, department_uz, department_ru) VALUES (?, ?, ?, ?)");
            $stmt->execute([$full_name, $position, $department_uz, $department_ru]);
            $message = 'Xodim muvaffaqiyatli qo\'shildi!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Xatolik yuz berdi!';
            $message_type = 'error';
        }
    }
}

// Xodimni tahrirlash
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    $full_name = sanitize($_POST['full_name'] ?? '');
    $position = sanitize($_POST['position'] ?? '');
    $department_uz = sanitize($_POST['department_uz'] ?? '');
    $department_ru = sanitize($_POST['department_ru'] ?? '');
    
    if ($id > 0 && !empty($full_name) && !empty($position)) {
        try {
            $stmt = $conn->prepare("UPDATE employees SET full_name = ?, position = ?, department_uz = ?, department_ru = ? WHERE id = ?");
            $stmt->execute([$full_name, $position, $department_uz, $department_ru, $id]);
            $message = 'Xodim muvaffaqiyatli yangilandi!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Xatolik yuz berdi!';
            $message_type = 'error';
        }
    }
}

// Xodim o'chirish
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Xodim o\'chirildi!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Xatolik yuz berdi!';
        $message_type = 'error';
    }
}

// Xodimlar ro'yxati
$employees_query = "SELECT id, full_name, position, department_uz, department_ru FROM employees ORDER BY position, full_name";
$employees_result = $conn->query($employees_query);
$employees = $employees_result->fetchAll();

// Barcha kafedralarni olish (dropdown uchun - o'qituvchilar uchun)
$departments_query = "SELECT DISTINCT department_uz, department_ru FROM employees WHERE position = 'teacher' AND department_uz IS NOT NULL AND department_uz != '' ORDER BY department_uz";
$departments_result = $conn->query($departments_query);
$departments = $departments_result->fetchAll();

// Barcha fakultetlarni olish (dropdown uchun - dekan va koordinatorlar uchun)
$faculties_query = "SELECT DISTINCT department_uz, department_ru FROM employees WHERE (position = 'dean' OR position = 'coordinator') AND department_uz IS NOT NULL AND department_uz != '' ORDER BY department_uz";
$faculties_result = $conn->query($faculties_query);
$faculties = $faculties_result->fetchAll();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xodimlarni Boshqarish</title>
    <link rel="stylesheet" href="<?php echo css('style.css'); ?>">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Xodimlarni Boshqarish</h1>
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
            <h2>Yangi xodim qo'shish</h2>
            <form method="POST" class="form-inline" id="addEmployeeForm">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <input type="text" name="full_name" placeholder="To'liq ism" required>
                </div>
                <div class="form-group">
                    <select name="position" id="positionSelect" required>
                        <option value="">Lavozimni tanlang</option>
                        <option value="teacher">O'qituvchi</option>
                        <option value="dean">Dekan</option>
                        <option value="coordinator">Koordinator</option>
                    </select>
                </div>
                <div class="form-group" id="departmentGroup" style="display: none;">
                    <select name="department_uz" id="departmentSelect">
                        <option value="">Kafedrani tanlang</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept['department_uz']); ?>" data-ru="<?php echo htmlspecialchars($dept['department_ru'] ?? ''); ?>">
                                <?php echo htmlspecialchars($dept['department_uz']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" id="facultyGroup" style="display: none;">
                    <select name="department_uz" id="facultySelect">
                        <option value="">Fakultetni tanlang</option>
                        <?php foreach ($faculties as $faculty): ?>
                            <option value="<?php echo htmlspecialchars($faculty['department_uz']); ?>" data-ru="<?php echo htmlspecialchars($faculty['department_ru'] ?? ''); ?>">
                                <?php echo htmlspecialchars($faculty['department_uz']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" id="departmentInputGroup" style="display: none;">
                    <input type="text" name="department_uz" id="departmentInputUz" placeholder="Kafedra/Fakultet (UZ)">
                </div>
                <div class="form-group" id="departmentRuGroup" style="display: none;">
                    <input type="text" name="department_ru" id="departmentInputRu" placeholder="Kafedra/Fakultet (RU)">
                </div>
                <button type="submit" class="btn btn-primary">Qo'shish</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>Xodimlar ro'yxati</h2>
            <?php if (count($employees) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ism</th>
                            <th>Lavozim</th>
                            <th>Bo'lim</th>
                            <th>Amallar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?php echo $employee['id']; ?></td>
                                <td><?php echo htmlspecialchars($employee['full_name']); ?></td>
                                <td><?php echo getPositionName($employee['position']); ?></td>
                                <td><?php echo htmlspecialchars($employee['department_uz'] ?? '-'); ?></td>
                                <td>
                                    <a href="employee_results?id=<?php echo $employee['id']; ?>" class="btn btn-small">Natijalar</a>
                                    <button type="button" class="btn btn-small" onclick="editEmployee(<?php echo htmlspecialchars(json_encode($employee)); ?>)">✏️ Tahrirlash</button>
                                    <a href="?delete=<?php echo $employee['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Rostdan o\'chirmoqchimisiz?')">O'chirish</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">Hozircha xodimlar ro'yxati bo'sh.</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <h2>Xodimni tahrirlash</h2>
            <form method="POST" id="editEmployeeForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <label for="editFullName">To'liq ism:</label>
                    <input type="text" name="full_name" id="editFullName" required>
                </div>
                <div class="form-group">
                    <label for="editPosition">Lavozim:</label>
                    <select name="position" id="editPosition" required>
                        <option value="teacher">O'qituvchi</option>
                        <option value="dean">Dekan</option>
                        <option value="coordinator">Koordinator</option>
                    </select>
                </div>
                <div class="form-group" id="editDepartmentGroup">
                    <label for="editDepartmentUz">Kafedra (UZ):</label>
                    <select name="department_uz" id="editDepartmentSelect">
                        <option value="">Kafedrani tanlang</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept['department_uz']); ?>" data-ru="<?php echo htmlspecialchars($dept['department_ru'] ?? ''); ?>">
                                <?php echo htmlspecialchars($dept['department_uz']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" id="editFacultyGroup" style="display: none;">
                    <label for="editFacultySelect">Fakultet:</label>
                    <select name="department_uz" id="editFacultySelect">
                        <option value="">Fakultetni tanlang</option>
                        <?php foreach ($faculties as $faculty): ?>
                            <option value="<?php echo htmlspecialchars($faculty['department_uz']); ?>" data-ru="<?php echo htmlspecialchars($faculty['department_ru'] ?? ''); ?>">
                                <?php echo htmlspecialchars($faculty['department_uz']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" id="editDepartmentInputGroup" style="display: none;">
                    <label for="editDepartmentInputUz">Kafedra/Fakultet (UZ):</label>
                    <input type="text" name="department_uz" id="editDepartmentInputUz">
                </div>
                <div class="form-group" id="editDepartmentRuGroup">
                    <label for="editDepartmentRu">Kafedra/Fakultet (RU):</label>
                    <input type="text" name="department_ru" id="editDepartmentRu">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Saqlash</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Bekor qilish</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Yangi xodim qo'shish formasi
        document.getElementById('positionSelect').addEventListener('change', function() {
            const position = this.value;
            const departmentGroup = document.getElementById('departmentGroup');
            const facultyGroup = document.getElementById('facultyGroup');
            const departmentInputGroup = document.getElementById('departmentInputGroup');
            const departmentRuGroup = document.getElementById('departmentRuGroup');
            
            if (position === 'teacher') {
                departmentGroup.style.display = 'block';
                facultyGroup.style.display = 'none';
                departmentInputGroup.style.display = 'none';
                departmentRuGroup.style.display = 'block';
                document.getElementById('departmentSelect').required = true;
                document.getElementById('facultySelect').required = false;
                document.getElementById('departmentInputUz').required = false;
            } else if (position === 'dean' || position === 'coordinator') {
                departmentGroup.style.display = 'none';
                facultyGroup.style.display = 'block';
                departmentInputGroup.style.display = 'none';
                departmentRuGroup.style.display = 'block';
                document.getElementById('departmentSelect').required = false;
                document.getElementById('facultySelect').required = true;
                document.getElementById('departmentInputUz').required = false;
            } else {
                departmentGroup.style.display = 'none';
                facultyGroup.style.display = 'none';
                departmentInputGroup.style.display = 'none';
                departmentRuGroup.style.display = 'none';
            }
        });
        
        // Kafedra dropdown o'zgarganda RU maydonini to'ldirish
        document.getElementById('departmentSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const ruValue = selectedOption.getAttribute('data-ru') || '';
            document.getElementById('departmentInputRu').value = ruValue;
        });
        
        // Fakultet dropdown o'zgarganda RU maydonini to'ldirish
        document.getElementById('facultySelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const ruValue = selectedOption.getAttribute('data-ru') || '';
            document.getElementById('departmentInputRu').value = ruValue;
        });
        
        // Edit modal funksiyalari
        function editEmployee(employee) {
            document.getElementById('editId').value = employee.id;
            document.getElementById('editFullName').value = employee.full_name;
            document.getElementById('editPosition').value = employee.position;
            
            // Kafedra maydonlarini to'ldirish
            const departmentUz = employee.department_uz || '';
            const departmentRu = employee.department_ru || '';
            
            // Lavozimga qarab kafedra/fakultet maydonini ko'rsatish
            updateEditDepartmentFields(employee.position);
            
            // Kafedra yoki fakultetni to'ldirish
            if (employee.position === 'teacher') {
                // O'qituvchi uchun kafedra dropdown'da qidirish
                const departmentSelect = document.getElementById('editDepartmentSelect');
                let found = false;
                for (let i = 0; i < departmentSelect.options.length; i++) {
                    if (departmentSelect.options[i].value === departmentUz) {
                        departmentSelect.value = departmentUz;
                        document.getElementById('editDepartmentRu').value = departmentSelect.options[i].getAttribute('data-ru') || departmentRu;
                        found = true;
                        break;
                    }
                }
                
                if (!found && departmentUz) {
                    // Agar dropdown'da topilmasa, input maydonini ko'rsatish
                    document.getElementById('editDepartmentSelect').value = '';
                    document.getElementById('editDepartmentInputUz').value = departmentUz;
                    document.getElementById('editDepartmentRu').value = departmentRu;
                    document.getElementById('editDepartmentGroup').style.display = 'none';
                    document.getElementById('editDepartmentInputGroup').style.display = 'block';
                }
            } else if (employee.position === 'dean' || employee.position === 'coordinator') {
                // Dekan yoki koordinator uchun fakultet dropdown'da qidirish
                const facultySelect = document.getElementById('editFacultySelect');
                let found = false;
                for (let i = 0; i < facultySelect.options.length; i++) {
                    if (facultySelect.options[i].value === departmentUz) {
                        facultySelect.value = departmentUz;
                        document.getElementById('editDepartmentRu').value = facultySelect.options[i].getAttribute('data-ru') || departmentRu;
                        found = true;
                        break;
                    }
                }
                
                if (!found && departmentUz) {
                    // Agar dropdown'da topilmasa, input maydonini ko'rsatish
                    document.getElementById('editFacultySelect').value = '';
                    document.getElementById('editDepartmentInputUz').value = departmentUz;
                    document.getElementById('editDepartmentRu').value = departmentRu;
                    document.getElementById('editFacultyGroup').style.display = 'none';
                    document.getElementById('editDepartmentInputGroup').style.display = 'block';
                }
            }
            
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Edit formada lavozim o'zgarganda
        document.getElementById('editPosition').addEventListener('change', function() {
            updateEditDepartmentFields(this.value);
        });
        
        function updateEditDepartmentFields(position) {
            const editDepartmentGroup = document.getElementById('editDepartmentGroup');
            const editFacultyGroup = document.getElementById('editFacultyGroup');
            const editDepartmentInputGroup = document.getElementById('editDepartmentInputGroup');
            const editDepartmentRuGroup = document.getElementById('editDepartmentRuGroup');
            
            if (position === 'teacher') {
                // O'qituvchi bo'lsa, kafedra dropdown yoki input ko'rsatish
                const currentDeptUz = document.getElementById('editDepartmentInputUz').value || 
                                     document.getElementById('editDepartmentSelect').value;
                const deptInDropdown = Array.from(document.getElementById('editDepartmentSelect').options)
                    .some(opt => opt.value === currentDeptUz && opt.value !== '');
                
                if (deptInDropdown && currentDeptUz) {
                    editDepartmentGroup.style.display = 'block';
                    editFacultyGroup.style.display = 'none';
                    editDepartmentInputGroup.style.display = 'none';
                } else {
                    // Agar dropdown'da yo'q bo'lsa, input ko'rsatish
                    if (currentDeptUz) {
                        document.getElementById('editDepartmentInputUz').value = currentDeptUz;
                    }
                    editDepartmentGroup.style.display = 'none';
                    editFacultyGroup.style.display = 'none';
                    editDepartmentInputGroup.style.display = 'block';
                }
                editDepartmentRuGroup.style.display = 'block';
            } else if (position === 'dean' || position === 'coordinator') {
                // Dekan yoki koordinator bo'lsa, fakultet dropdown yoki input ko'rsatish
                const currentDeptUz = document.getElementById('editDepartmentInputUz').value || 
                                     document.getElementById('editFacultySelect').value;
                const deptInDropdown = Array.from(document.getElementById('editFacultySelect').options)
                    .some(opt => opt.value === currentDeptUz && opt.value !== '');
                
                if (deptInDropdown && currentDeptUz) {
                    editDepartmentGroup.style.display = 'none';
                    editFacultyGroup.style.display = 'block';
                    editDepartmentInputGroup.style.display = 'none';
                } else {
                    // Agar dropdown'da yo'q bo'lsa, input ko'rsatish
                    if (currentDeptUz) {
                        document.getElementById('editDepartmentInputUz').value = currentDeptUz;
                    }
                    editDepartmentGroup.style.display = 'none';
                    editFacultyGroup.style.display = 'none';
                    editDepartmentInputGroup.style.display = 'block';
                }
                editDepartmentRuGroup.style.display = 'block';
            } else {
                editDepartmentGroup.style.display = 'none';
                editFacultyGroup.style.display = 'none';
                editDepartmentInputGroup.style.display = 'none';
                editDepartmentRuGroup.style.display = 'none';
            }
        }
        
        // Edit formada kafedra dropdown o'zgarganda
        document.getElementById('editDepartmentSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const ruValue = selectedOption.getAttribute('data-ru') || '';
            document.getElementById('editDepartmentRu').value = ruValue;
        });
        
        // Edit formada fakultet dropdown o'zgarganda
        document.getElementById('editFacultySelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const ruValue = selectedOption.getAttribute('data-ru') || '';
            document.getElementById('editDepartmentRu').value = ruValue;
        });
        
        // Modal tashqarisiga bosilganda yopish
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>

