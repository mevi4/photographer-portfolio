<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $captcha = trim($_POST['captcha'] ?? '');

    // Простейшая капча
    if ($captcha != '5') {
        $_SESSION['error'] = 'Неверный ответ на капчу.';
        redirect('index.php');
    }

    if (empty($nickname) || empty($content)) {
        $_SESSION['error'] = 'Заполните все поля.';
        redirect('index.php');
    }

    // Вставляем отзыв
    $stmt = $pdo->prepare("INSERT INTO review (nickname, content) VALUES (?, ?)");
    $stmt->execute([$nickname, $content]);

    $_SESSION['success'] = 'Спасибо! Отзыв отправлен на модерацию.';
    redirect('index.php');
} else {
    redirect('index.php');
}