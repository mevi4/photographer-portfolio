<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Защита от CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['contact_error'] = 'Ошибка безопасности.';
        redirect('index.php');
    }

    //2. Проверка капчи-пазл
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

    // 3. Ограничение частоты отправки (не чаще раза в минуту)
    if (!isset($_SESSION['last_contact_time'])) {
        $_SESSION['last_contact_time'] = 0;
    }
    if (time() - $_SESSION['last_contact_time'] < 60) {
        $_SESSION['contact_error'] = 'Слишком частые заявки. Попробуйте через минуту.';
        redirect('index.php');
    }

    $phone = trim($_POST['phone'] ?? '');

    // Проверка на пустое поле
    if (empty($phone)) {
        $_SESSION['contact_error'] = 'Введите номер телефона.';
        redirect('index.php');
    }

    // Очистка номера
    $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
    $digitsOnly = preg_replace('/[^0-9]/', '', $cleanPhone);

    // Проверка длины
    if (strlen($digitsOnly) < 10 || strlen($digitsOnly) > 12) {
        $_SESSION['contact_error'] = 'Введите корректный номер телефона (10-12 цифр). Пример: +7 999 123-45-67';
        redirect('index.php');
    }

    $firstDigit = substr($digitsOnly, 0, 1);
    if (!in_array($firstDigit, ['7', '8', '9'])) {
        $_SESSION['contact_error'] = 'Номер должен начинаться с 7, 8 или 9.';
        redirect('index.php');
    }

    // Приведение к единому формату +7XXXXXXXXXX
    if (strlen($digitsOnly) == 10) {
        $formattedPhone = '+7' . $digitsOnly;
    } elseif (strlen($digitsOnly) == 11 && $digitsOnly[0] == '7') {
        $formattedPhone = '+' . $digitsOnly;
    } elseif (strlen($digitsOnly) == 11 && $digitsOnly[0] == '8') {
        $formattedPhone = '+7' . substr($digitsOnly, 1);
    } elseif (strlen($digitsOnly) == 12 && $digitsOnly[0] == '7') {
        $formattedPhone = '+' . $digitsOnly;
    } else {
        $formattedPhone = '+' . $digitsOnly;
    }

    // Проверка на слишком частые заявки с одного номера (3 в сутки)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_request WHERE phone_number = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $stmt->execute([$formattedPhone]);
    if ($stmt->fetchColumn() > 3) {
        $_SESSION['contact_error'] = 'Слишком много заявок с этого номера. Попробуйте завтра.';
        redirect('index.php');
    }

    // Сохранение заявки
    try {
        $stmt = $pdo->prepare("INSERT INTO contact_request (phone_number) VALUES (?)");
        $stmt->execute([$formattedPhone]);

        $_SESSION['last_contact_time'] = time();
        $_SESSION['contact_success'] = 'Заявка отправлена! Я свяжусь с вами в ближайшее время.';
    } catch (PDOException $e) {
        error_log('Contact insert error: ' . $e->getMessage());
        $_SESSION['contact_error'] = 'Ошибка при отправке заявки. Попробуйте позже.';
    }

    redirect('index.php');
} else {
    redirect('index.php');
}