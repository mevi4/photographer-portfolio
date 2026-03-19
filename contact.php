<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');

    if (empty($phone)) {
        $_SESSION['error'] = 'Введите номер телефона.';
        redirect('index.php#contacts');
    }

    // Простейшая валидация (можно улучшить)
    $stmt = $pdo->prepare("INSERT INTO contact_request (phone_number) VALUES (?)");
    $stmt->execute([$phone]);

    $_SESSION['success'] = 'Заявка отправлена! Мы свяжемся с вами в ближайшее время.';
    redirect('index.php#contacts');
} else {
    redirect('index.php');
}