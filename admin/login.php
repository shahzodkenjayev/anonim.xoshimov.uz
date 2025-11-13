<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Agar login qilgan bo'lsa, tegishli sahifaga yo'naltirish
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: dashboard.php');
    } else {
        header('Location: ../student/survey.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, username, password, full_name, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Faqat adminlar kirishi mumkin
            if ($user['role'] !== 'admin') {
                $error = 'Bu sahifa faqat adminlar uchun. <a href="../student/login.php">Talaba login</a>';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: dashboard.php');
                exit;
            }
        } else {
            $error = 'Noto\'g\'ri foydalanuvchi nomi yoki parol!';
        }
    } else {
        $error = 'Barcha maydonlarni to\'ldiring!';
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Kirish</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Admin login sahifasi uchun maxsus stillar */
        body {
            background: #f5f7fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        
        .admin-login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .admin-login-box {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #e8e8e8;
        }
        
        .admin-login-box h1 {
            color: #4a90e2;
            text-align: center;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 600;
        }
        
        .admin-login-box h2 {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 18px;
            font-weight: normal;
        }
        
        .admin-login-box .form-group {
            margin-bottom: 20px;
        }
        
        .admin-login-box .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 14px;
        }
        
        .admin-login-box .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        
        .admin-login-box .form-group input:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }
        
        .admin-login-box .btn-primary {
            width: 100%;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
        }
        
        .admin-login-box .login-links {
            margin-top: 20px;
            text-align: center;
        }
        
        .admin-login-box .login-links a {
            color: #4a90e2;
            text-decoration: none;
            font-size: 14px;
        }
        
        .admin-login-box .login-links a:hover {
            text-decoration: underline;
        }
        
        .admin-login-box .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .admin-login-box .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-box">
            <h1>Admin Panel</h1>
            <h2>Tizimga kirish</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Foydalanuvchi nomi:</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Parol:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Kirish</button>
            </form>
            
            <div class="login-links">
                <p><a href="../index.php">← Asosiy sahifaga qaytish</a></p>
            </div>
        </div>
    </div>
</body>
</html>

