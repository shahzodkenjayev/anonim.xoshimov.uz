<?php
/**
 * Admin panel index sahifasi
 * Agar login qilgan bo'lsa, dashboard'ga yo'naltirish
 * Aks holda login sahifasiga yo'naltirish
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Agar login qilgan bo'lsa, dashboard'ga yo'naltirish
if (isLoggedIn() && isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

// Aks holda login sahifasiga yo'naltirish
header('Location: login.php');
exit;
?>

