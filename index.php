<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Agar login qilgan bo'lsa, tegishli sahifaga yo'naltirish
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: student/survey.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anonim So'rovnoma Platformasi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="home-page">
            <div class="hero-section">
                <h1>Anonim So'rovnoma Platformasi</h1>
                <p class="subtitle">O'qituvchilar, dekanlar va koordinatorlar haqida fikringizni bildiring</p>
            </div>
            
            <div class="options-grid">
                <div class="option-card">
                    <div class="option-icon">🗳️</div>
                    <h2>Anonim Ovoz Berish</h2>
                    <p>Xodimlar haqida anonim so'rovnoma to'ldiring. Login qilish shart emas.</p>
                    <a href="anonymous.php" class="btn btn-primary btn-large">Ovoz Berish</a>
                </div>
                
                <div class="option-card">
                    <div class="option-icon">🔐</div>
                    <h2>Tizimga Kirish</h2>
                    <p>Talaba yoki admin hisobi bilan tizimga kiring va batafsil so'rovnoma to'ldiring.</p>
                    <a href="student/login.php" class="btn btn-secondary btn-large">Kirish</a>
                </div>
            </div>
            
            <div class="info-section">
                <h3>Qanday Ishlaydi?</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>1. Anonim Ovoz Berish</strong>
                        <p>Login qilmasdan, to'g'ridan-to'g'ri xodimlarni baholash. Sizning shaxsingiz aniq qilinmaydi.</p>
                    </div>
                    <div class="info-item">
                        <strong>2. Tizimga Kirish</strong>
                        <p>Talaba hisobi bilan kirib, batafsil so'rovnoma to'ldiring. Har bir xodimga bir marta javob bera olasiz.</p>
                    </div>
                    <div class="info-item">
                        <strong>3. Admin Panel</strong>
                        <p>Adminlar natijalarni ko'rish va boshqarish uchun alohida sahifadan kirishadi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
