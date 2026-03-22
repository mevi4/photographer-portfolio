<?php
require_once 'includes/header.php';

// Получаем неподтверждённые отзывы
$stmt = $pdo->query("SELECT * FROM review WHERE is_approved = 0 ORDER BY created_at DESC");
$pending = $stmt->fetchAll();

// Обработка действий с CSRF защитой
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['review_id'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Ошибка безопасности.';
        redirect('index.php');
    }

    $review_id = (int) $_POST['review_id'];
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
    <div class="alert alert-success"><?= e($_SESSION['success']);
    unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error"><?= e($_SESSION['error']);
    unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if (count($pending) > 0): ?>
    <div class="table-responsive">
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
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                <button type="submit" name="action" value="approve" class="btn-approve">
                                    <span class="btn-icon">✓</span> Одобрить
                                </button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                <button type="submit" name="action" value="delete" class="btn-delete"
                                    onclick="return confirm('Удалить отзыв?')">
                                    <span class="btn-icon">🗑️</span> Удалить
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>Нет отзывов, ожидающих модерации.</p>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>