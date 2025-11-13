<?php
// Xavfsizlik funksiyalari

// IP manzilini olish
function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    
    // Agar bir nechta IP bo'lsa, birinchisini olish
    if (strpos($ipaddress, ',') !== false) {
        $ipaddress = explode(',', $ipaddress)[0];
    }
    
    return trim($ipaddress);
}

// Rate limiting - bir vaqtda ko'p so'rov yuborishni cheklash
function checkRateLimit($action = 'default', $maxRequests = 10, $timeWindow = 60) {
    $ip = getClientIP();
    $key = 'rate_limit_' . $action . '_' . md5($ip);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'count' => 0,
            'reset_time' => time() + $timeWindow
        ];
    }
    
    // Vaqt o'tib ketgan bo'lsa, qayta boshlash
    if (time() > $_SESSION[$key]['reset_time']) {
        $_SESSION[$key] = [
            'count' => 0,
            'reset_time' => time() + $timeWindow
        ];
    }
    
    // Limitdan oshib ketgan bo'lsa
    if ($_SESSION[$key]['count'] >= $maxRequests) {
        logSecurityEvent('rate_limit_exceeded', [
            'ip' => $ip,
            'action' => $action,
            'count' => $_SESSION[$key]['count']
        ]);
        return false;
    }
    
    // So'rov sonini oshirish
    $_SESSION[$key]['count']++;
    return true;
}

// CSRF token yaratish
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF token tekshirish
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// IP bloklash tekshiruvi
function isIPBlocked($ip = null) {
    if ($ip === null) {
        $ip = getClientIP();
    }
    
    // Bloklangan IP'lar ro'yxati (database yoki fayldan olish mumkin)
    $blockedIPs = [];
    
    // Agar database'da bloklangan IP'lar jadvali bo'lsa, u yerdan olish
    // Hozircha oddiy array ishlatamiz
    
    return in_array($ip, $blockedIPs);
}

// Oddiy matematik CAPTCHA yaratish (har bir forma uchun alohida key)
function generateSimpleCaptcha($formId = null) {
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $operation = rand(0, 1) ? '+' : '-';
    
    if ($operation === '+') {
        $answer = $num1 + $num2;
        $question = "$num1 + $num2";
    } else {
        // Ayirishda manfiy javob bo'lmasligi uchun
        if ($num1 < $num2) {
            $temp = $num1;
            $num1 = $num2;
            $num2 = $temp;
        }
        $answer = $num1 - $num2;
        $question = "$num1 - $num2";
    }
    
    // Har bir forma uchun alohida key
    $captchaKey = 'captcha_' . ($formId ?? 'default');
    $_SESSION[$captchaKey . '_answer'] = $answer;
    $_SESSION[$captchaKey . '_time'] = time();
    
    return [
        'question' => $question,
        'answer' => $answer,
        'key' => $captchaKey
    ];
}

// CAPTCHA javobini tekshirish
function verifyCaptcha($userAnswer, $formId = null) {
    $captchaKey = 'captcha_' . ($formId ?? 'default');
    
    if (!isset($_SESSION[$captchaKey . '_answer']) || !isset($_SESSION[$captchaKey . '_time'])) {
        return false;
    }
    
    // CAPTCHA 10 daqiqadan eski bo'lsa, yaroqsiz
    if (time() - $_SESSION[$captchaKey . '_time'] > 600) {
        unset($_SESSION[$captchaKey . '_answer'], $_SESSION[$captchaKey . '_time']);
        return false;
    }
    
    $correctAnswer = $_SESSION[$captchaKey . '_answer'];
    $isValid = intval($userAnswer) === intval($correctAnswer);
    
    // Tekshirilgandan keyin o'chirish
    unset($_SESSION[$captchaKey . '_answer'], $_SESSION[$captchaKey . '_time']);
    
    return $isValid;
}

// Xavfsizlik hodisalarini yozib olish
function logSecurityEvent($event, $data = []) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $logEntry = sprintf(
        "[%s] IP: %s | Event: %s | Data: %s | User-Agent: %s\n",
        $timestamp,
        $ip,
        $event,
        json_encode($data),
        $userAgent
    );
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Input validation kuchaytirish
function validateInput($data, $type = 'text', $maxLength = 1000) {
    $data = trim($data);
    
    // Bo'sh bo'lmasligi
    if (empty($data)) {
        return false;
    }
    
    // Uzunlik tekshiruvi
    if (strlen($data) > $maxLength) {
        return false;
    }
    
    // SQL injection belgilari
    $dangerous = ['--', ';', '/*', '*/', 'xp_', 'sp_', 'exec', 'union', 'select', 'insert', 'update', 'delete', 'drop', 'create', 'alter'];
    foreach ($dangerous as $pattern) {
        if (stripos($data, $pattern) !== false) {
            logSecurityEvent('sql_injection_attempt', ['input' => substr($data, 0, 100)]);
            return false;
        }
    }
    
    // Type bo'yicha tekshirish
    switch ($type) {
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT) !== false;
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
        case 'url':
            return filter_var($data, FILTER_VALIDATE_URL) !== false;
        case 'text':
        default:
            return true;
    }
}

// XSS himoyasi
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Shubhali faoliyatni aniqlash
function detectSuspiciousActivity($ip = null) {
    if ($ip === null) {
        $ip = getClientIP();
    }
    
    $suspicious = false;
    $reasons = [];
    
    // Rate limit tekshiruvi
    if (!checkRateLimit('anonymous_vote', 5, 60)) {
        $suspicious = true;
        $reasons[] = 'rate_limit_exceeded';
    }
    
    // User-Agent tekshiruvi (bot belgilari)
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $botPatterns = ['bot', 'crawler', 'spider', 'scraper'];
    foreach ($botPatterns as $pattern) {
        if (stripos($userAgent, $pattern) !== false) {
            $suspicious = true;
            $reasons[] = 'bot_detected';
            break;
        }
    }
    
    if ($suspicious) {
        logSecurityEvent('suspicious_activity', [
            'ip' => $ip,
            'reasons' => $reasons,
            'user_agent' => $userAgent
        ]);
    }
    
    return $suspicious;
}

// Session xavfsizligini yaxshilash
function secureSession() {
    // Session cookie xavfsizligi
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    
    // Session timeout (30 daqiqa)
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    } else {
        if (time() - $_SESSION['last_activity'] > 1800) {
            session_destroy();
            session_start();
        }
        $_SESSION['last_activity'] = time();
    }
}

// Request metodini tekshirish
function validateRequestMethod($allowedMethods = ['GET', 'POST']) {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, $allowedMethods)) {
        logSecurityEvent('invalid_request_method', ['method' => $method]);
        http_response_code(405);
        die('Method Not Allowed');
    }
    return $method;
}
?>

