<?php
// Umumiy funksiyalar

// Foydalanuvchini tekshirish
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Admin ekanligini tekshirish
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Xavfsizlik uchun input tozalash
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Xodim pozitsiyasini o'zbek tilida ko'rsatish
function getPositionName($position) {
    $positions = [
        'teacher' => 'O\'qituvchi',
        'dean' => 'Dekan',
        'coordinator' => 'Koordinator'
    ];
    return $positions[$position] ?? $position;
}

// Baholash yulduzchalari
function renderStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '★';
        } else {
            $stars .= '☆';
        }
    }
    return $stars;
}

// Savol matnini til bo'yicha olish
function getQuestionText($question, $lang = 'uz') {
    if ($lang === 'ru' && !empty($question['question_text_ru'])) {
        return $question['question_text_ru'];
    }
    // Default: O'zbek tili yoki mavjud bo'lmasa, eski question_text
    return $question['question_text_uz'] ?? $question['question_text'] ?? '';
}

// Foydalanuvchi tilini aniqlash (session yoki default)
function getUserLanguage() {
    return $_SESSION['language'] ?? 'uz';
}

// Tilni o'rnatish
function setUserLanguage($lang) {
    $_SESSION['language'] = $lang;
}
?>

