<?php
require_once 'includes/header.php';

// Получаем неподтверждённые отзывы
$stmt = $pdo->query("SELECT * FROM review WHERE is_approved = 0 ORDER BY created_at DESC");
$pending = $stmt->fetchAll();

// Обработка действий с CSRF защитой
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['review_id'])) {
    // Проверка CSRF токена
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Ошибка безопасности.';
        redirect('index.php');
    }
    
    $review_id = (int)$_POST['review_id'];
    if ($_POST['action'] === 'approve') {
        $stmt = $pdo->prepare("UPDATE review SET is_approved = 1, moderated_by = ?, moderated_at = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $review_id]);
        $_SESSION['success'] = 'Отзыв одобрен.';
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM review WHERE id = ?");
        $stmt->execute([$review_id]);
        $_SESSION['success'] = 'Отзыв удалён.';
    }
    redirect('index.php');
}
?>

<h2>Отзывы на модерации</h2>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error"><?= e($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if (count($pending) > 0): ?>
    <div class="table-responsive">
        <form method="post" id="reviewsForm">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <table>
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Никнейм</th>
                        <th>Отзыв</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $review): ?>
                    <tr>
                        <td><?= e(date('d.m.Y H:i', strtotime($review['created_at']))) ?></td>
                        <td><?= e($review['nickname']) ?></td>
                        <td><?= nl2br(e($review['content'])) ?></td>
                        <td class="actions">
                            <button type="submit" name="action" value="approve" formaction="?review_id=<?= $review['id'] ?>" 
                                    class="btn-approve" onclick="this.form.action='?action=approve&review_id=<?= $review['id'] ?>'">
                                <span class="btn-icon">✓</span> Одобрить
                            </button>
                            <button type="submit" name="action" value="delete" formaction="?review_id=<?= $review['id'] ?>" 
                                    class="btn-delete" onclick="return confirm('Удалить отзыв?')">
                                <span class="btn-icon">🗑️</span> Удалить
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    </div>
<?php else: ?>
    <p>Нет отзывов, ожидающих модерации.</p>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>