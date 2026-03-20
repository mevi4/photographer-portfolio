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
if (!isset($_SESSION['last_contact_time'])) {
    $_SESSION['last_contact_time'] = 0;
}

if (time() - $_SESSION['last_contact_time'] < 60) {
    $_SESSION['contact_error'] = 'Слишком частые заявки. Попробуйте через минуту.';
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');

    // 1. Проверка на пустое поле
    if (empty($phone)) {
        $_SESSION['contact_error'] = 'Введите номер телефона.';
        redirect('index.php');
    }

    // 2. Удаляем все лишние символы (пробелы, скобки, тире)
    $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
    
    // 3. Проверяем, что остались только цифры и возможно +
    $digitsOnly = preg_replace('/[^0-9]/', '', $cleanPhone);
    
    // 4. Проверяем длину (российские номера: 10 или 11 цифр без +7, или 12 с +7)
    if (strlen($digitsOnly) < 10 || strlen($digitsOnly) > 12) {
        $_SESSION['contact_error'] = 'Введите корректный номер телефона (10-12 цифр). Пример: +7 999 123-45-67';
        redirect('index.php');
    }
    
    // 5. Проверяем, что номер начинается с правильного кода
    $firstDigit = substr($digitsOnly, 0, 1);
    if ($firstDigit !== '7' && $firstDigit !== '8' && $firstDigit !== '9') {
        $_SESSION['contact_error'] = 'Номер должен начинаться с 7, 8 или 9.';
        redirect('index.php');
    }
    
    // 6. Приводим к единому формату +7XXXXXXXXXX
    if (strlen($digitsOnly) == 10) {
        // 10 цифр - добавляем +7
        $formattedPhone = '+7' . $digitsOnly;
    } elseif (strlen($digitsOnly) == 11 && $digitsOnly[0] == '7') {
        // 11 цифр, начинается с 7 - добавляем +
        $formattedPhone = '+' . $digitsOnly;
    } elseif (strlen($digitsOnly) == 11 && $digitsOnly[0] == '8') {
        // 11 цифр, начинается с 8 - заменяем 8 на +7
        $formattedPhone = '+7' . substr($digitsOnly, 1);
    } elseif (strlen($digitsOnly) == 12 && $digitsOnly[0] == '7') {
        // 12 цифр, начинается с 7 (уже с кодом страны)
        $formattedPhone = '+' . $digitsOnly;
    } else {
        $formattedPhone = '+' . $digitsOnly;
    }
    
    // 7. Проверяем, не слишком ли много заявок с этого номера за последние 24 часа
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_request WHERE phone_number = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $stmt->execute([$formattedPhone]);
    if ($stmt->fetchColumn() > 3) {
        $_SESSION['contact_error'] = 'Слишком много заявок с этого номера. Попробуйте завтра.';
        redirect('index.php');
    }
    
    // 8. Сохраняем заявку
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