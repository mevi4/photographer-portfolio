<?php
require_once 'config.php';

// Защита от CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Ошибка безопасности. Пожалуйста, обновите страницу и попробуйте снова.';
        redirect('index.php');
    }
}

// Ограничение частоты отправки
if (!checkRateLimit('review', 3, 300)) { // 3 отзыва за 5 минут
    $_SESSION['error'] = 'Слишком много отзывов. Подождите 5 минут.';
    redirect('index.php');
}

// Защита от ботов (капча уже есть)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = sanitizeInput($_POST['nickname'] ?? '');
    $content = sanitizeInput($_POST['content'] ?? '');

    if (!isset($_POST['puzzle_solved']) || $_POST['puzzle_solved'] != '1') {
        $_SESSION['contact_error'] = 'Соберите пазл, чтобы отправить заявку.';
        redirect('index.php');
        exit; // Важно выйти после редиректа
    }

    if (!verifyPuzzleCaptcha()) {
        $_SESSION['contact_error'] = 'Ошибка капчи. Попробуйте ещё раз.';
        redirect('index.php');
        exit; // Важно выйти после редиректа
    }

    // --- УДАЛЕНИЕ СЕССИИ ПОСЛЕ УСПЕШНОЙ ПРОВЕРКИ ---
    unset($_SESSION['puzzle_hole']);
    unset($_SESSION['puzzle_solved']);

    // Валидация длины
    if (strlen($nickname) < 2 || strlen($nickname) > 50) {
        $_SESSION['error'] = 'Имя должно содержать от 2 до 50 символов.';
        redirect('index.php');
    }

    if (strlen($content) < 5 || strlen($content) > 1000) {
        $_SESSION['error'] = 'Отзыв должен содержать от 5 до 1000 символов.';
        redirect('index.php');
    }

    // Запрещаем HTML теги и ссылки
    if ($nickname !== strip_tags($nickname) || $content !== strip_tags($content)) {
        $_SESSION['error'] = 'HTML теги и ссылки запрещены.';
        redirect('index.php');
    }

    // Проверка на спам (повторяющиеся символы)
    if (preg_match('/(.)\1{4,}/', $content)) {
        $_SESSION['error'] = 'Слишком много повторяющихся символов.';
        redirect('index.php');
    }

    // Проверка на ссылки
    if (preg_match('/https?:\/\/|www\./i', $content)) {
        $_SESSION['error'] = 'Ссылки в отзывах запрещены.';
        redirect('index.php');
    }

    // Вставляем отзыв
    try {
        $stmt = $pdo->prepare("INSERT INTO review (nickname, content) VALUES (?, ?)");
        $stmt->execute([$nickname, $content]);

        $_SESSION['success'] = 'Спасибо! Отзыв отправлен на модерацию.';
    } catch (PDOException $e) {
        error_log('Review insert error: ' . $e->getMessage());
        $_SESSION['error'] = 'Ошибка при сохранении отзыва. Попробуйте позже.';
    }

    redirect('index.php');
} else {
    redirect('index.php');
}