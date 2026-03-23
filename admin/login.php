<?php
require_once __DIR__ . '/../config.php';

// Защита от брутфорса
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_login_attempt'] = 0;
}

if ($_SESSION['login_attempts'] >= 5) {
    $time_passed = time() - $_SESSION['last_login_attempt'];
    if ($time_passed < 900) { // 15 минут блокировки
        die('Слишком много попыток входа. Попробуйте через 15 минут.');
    } else {
        $_SESSION['login_attempts'] = 0;
    }
}

if (isLoggedIn()) {
    redirect('index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Регенерация ID сессии для защиты
    session_regenerate_id(true);

    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_activity'] = time();
        redirect('index.php');
        exit;
    } else {
        $_SESSION['login_attempts']++;
        $_SESSION['last_login_attempt'] = time();
        // Убираем информацию о количестве оставшихся попыток
        $error = 'Неверное имя пользователя или пароль.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #fafafa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
        }

        h1 {
            font-size: 32px;
            font-weight: 400;
            margin-bottom: 10px;
            color: #000;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #000;
        }

        button {
            width: 100%;
            padding: 15px;
            background: #000;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        button:hover {
            background: #333;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c62828;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #999;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            color: #000;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h1>Админ-панель</h1>
        <div class="subtitle">Вход для фотографа и помощника</div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Имя пользователя</label>
                <input type="text" id="username" name="username" required
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="Введите логин">
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required placeholder="Введите пароль">
            </div>

            <button type="submit">Войти</button>
        </form>

        <a href="../index.php" class="back-link">← Вернуться на главную</a>
    </div>
</body>

</html>