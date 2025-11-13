<?php
/**
 * Admin parol hash yaratish skripti
 * Bu skript admin123 paroli uchun to'g'ri hash yaratadi
 */

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Parol: admin123\n";
echo "Hash: $hash\n\n";

// Tekshirish
if (password_verify($password, $hash)) {
    echo "✅ Hash to'g'ri!\n";
} else {
    echo "❌ Hash noto'g'ri!\n";
}

echo "\nSQL buyrug'i:\n";
echo "UPDATE users SET password = '$hash' WHERE username = 'admin';\n";
echo "yoki\n";
echo "INSERT INTO users (username, password, full_name, role) VALUES ('admin', '$hash', 'Administrator', 'admin');\n";

