<?php
$password = "admin123";

// Создание хэша
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Хэш: " . $hash . "\n";

// Проверка пароля
if (password_verify($password, $hash)) {
    echo "Пароль верный!\n";
} else {
    echo "Неверный пароль!\n";
}
?>