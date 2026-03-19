<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
    exit;
}
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
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
            color: #1a1a1a;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Сайдбар */
        .sidebar {
            width: 260px;
            background: #000;
            color: #fff;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 30px 25px;
            border-bottom: 1px solid #333;
        }
        
        .sidebar-header h3 {
            font-size: 18px;
            font-weight: 400;
            letter-spacing: -0.3px;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            font-size: 13px;
            color: #999;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .sidebar-nav a {
            color: #ccc;
            display: block;
            padding: 12px 25px;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 14px;
            border-left: 2px solid transparent;
        }
        
        .sidebar-nav a:hover {
            background: #1a1a1a;
            border-left-color: #fff;
            color: #fff;
        }
        
        /* Контент */
        .content {
            flex: 1;
            margin-left: 260px;
            padding: 40px;
            background: #fafafa;
        }
        
        .content h2 {
            font-size: 32px;
            font-weight: 400;
            margin-bottom: 30px;
            color: #000;
            letter-spacing: -0.5px;
        }
        
        /* Карточки */
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            border: 1px solid #eee;
        }
        
        .card h3 {
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #000;
        }
        
        /* Таблицы */
        .table-responsive {
            overflow-x: auto;
            background: #fff;
            border-radius: 12px;
            border: 1px solid #eee;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        
        table th {
            background: #fafafa;
            color: #666;
            font-weight: 500;
            padding: 15px 20px;
            text-align: left;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #eee;
        }
        
        table td {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        table tr:hover {
            background: #fafafa;
        }
        
        /* Кнопки */
        .btn {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }
        
        .btn-primary {
            background: #000;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #333;
        }
        
        .btn-success {
            background: #2e7d32;
            color: #fff;
        }
        
        .btn-success:hover {
            background: #1b5e20;
        }
        
        .btn-danger {
            background: #c62828;
            color: #fff;
        }
        
        .btn-danger:hover {
            background: #b71c1c;
        }
        
        /* Формы */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #000;
            box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
        }
        
        /* Сообщения */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        /* Стили для кнопок действий */
.actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.actions form {
    margin: 0;
}

.btn-approve, .btn-delete {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.btn-approve {
    background: #f0f9f0;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}

.btn-approve:hover {
    background: #2e7d32;
    color: white;
    border-color: #2e7d32;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(46, 125, 50, 0.2);
}

.btn-delete {
    background: #fef2f2;
    color: #c62828;
    border: 1px solid #ffcdd2;
}

.btn-delete:hover {
    background: #c62828;
    color: white;
    border-color: #c62828;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(198, 40, 40, 0.2);
}

.btn-icon {
    font-size: 16px;
    line-height: 1;
}

.btn-text {
    font-size: 13px;
}

/* Для мобильных устройств */
@media (max-width: 768px) {
    .actions {
        flex-direction: column;
        gap: 5px;
    }
    
    .btn-approve, .btn-delete {
        width: 100%;
        justify-content: center;
    }
}

        /* Сетка фото */
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .photo-card {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #eee;
        }
        
        .photo-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .photo-card-body {
            padding: 15px;
        }
        
        .photo-card-actions {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .content {
                margin-left: 0;
                padding: 20px;
            }
            
            .admin-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Админ-панель</h3>
            <p><?= e($_SESSION['username'] ?? 'Пользователь') ?></p>
        </div>
        <div class="sidebar-nav">
            <a href="index.php">📝 Отзывы</a>
            <a href="albums.php">📸 Альбомы</a>
            <a href="requests.php">📞 Заявки</a>
            <a href="logout.php" style="border-top: 1px solid #333; margin-top: 20px;">🚪 Выход</a>
        </div>
    </div>
    <div class="content">