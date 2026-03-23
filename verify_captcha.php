<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'solve') {
    // Проверяем CSRF-токен, если хотите повысить безопасность
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'error' => 'CSRF token mismatch']);
        exit;
    }
    $_SESSION['puzzle_solved'] = true;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}