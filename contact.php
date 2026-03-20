<?php
require_once 'config.php';

// Защита от CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['contact_error'] = 'Ошибка безопасности.';
        redirect('index.php');
    }
}

// Ограничение частоты отправки
$ip = $_SERVER['REMOTE_ADDR'];
session_start();
if (!isset($_SESSION['last_contact_time'])) {
    $_SESSION['last_contact_time'] = 0;
}

if (time() - $_SESSION['last_contact_time'] < 60) { // Не чаще 1 раза в минуту
    $_SESSION['contact_error'] = 'Слишком частые заявки. Попробуйте через минуту.';
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');

    if (empty($phone)) {
        $_SESSION['contact_error'] = 'Введите номер телефона.';
        redirect('index.php');
    }

    // Удаляем все кроме цифр и плюса
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    // Валидация российских номеров
    $phonePattern = '/^(\+7|8|7)?[\s\-]?\(?[0-9]{3}\)?[\s\-]?[0-9]{3}[\s\-]?[0-9]{2}[\s\-]?[0-9]{2}$/';
    
    // Более простая валидация - проверяем, что после очистки осталось 10-12 цифр
    $digitsOnly = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($digitsOnly) < 10 || strlen($digitsOnly) > 12) {
        $_SESSION['contact_error'] = 'Введите корректный номер телефона (10-12 цифр).';
        redirect('index.php');
    }

    // Приводим к единому формату для хранения
    if (strlen($digitsOnly) == 10) {
        $phone = '+7' . $digitsOnly;
    } elseif (strlen($digitsOnly) == 11 && $digitsOnly[0] == '7') {
        $phone = '+' . $digitsOnly;
    } elseif (strlen($digitsOnly) == 11 && $digitsOnly[0] == '8') {
        $phone = '+7' . substr($digitsOnly, 1);
    } else {
        $phone = '+' . $digitsOnly;
    }

    // Проверяем, не отправлял ли этот номер заявки слишком часто
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_request WHERE phone_number = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $stmt->execute([$phone]);
    if ($stmt->fetchColumn() > 3) {
        $_SESSION['contact_error'] = 'Слишком много заявок с этого номера. Попробуйте завтра.';
        redirect('index.php');
    }

    // Сохраняем заявку
    $stmt = $pdo->prepare("INSERT INTO contact_request (phone_number) VALUES (?)");
    
    try {
        $stmt->execute([$phone]);
        $_SESSION['last_contact_time'] = time();
        $_SESSION['contact_success'] = 'Заявка отправлена! Я свяжусь с вами в ближайшее время.';
    } catch (PDOException $e) {
        error_log('Contact insert error: ' . $e->getMessage());
        $_SESSION['contact_error'] = 'Ошибка при отправке заявки.';
    }
    
    redirect('index.php');
} else {
    redirect('index.php');
}