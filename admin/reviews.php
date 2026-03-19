<?php
require_once 'includes/header.php';

// Получаем все отзывы
$stmt = $pdo->query("
    SELECT r.*, u.username as moderator_name 
    FROM review r
    LEFT JOIN user u ON r.moderated_by = u.id
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll();

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['review_id'])) {
    $review_id = (int)$_POST['review_id'];
    
    if ($_POST['action'] === 'approve') {
        $stmt = $pdo->prepare("UPDATE review SET is_approved = 1, moderated_by = ?, moderated_at = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $review_id]);
        $_SESSION['success'] = 'Отзыв одобрен';
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM review WHERE id = ?");
        $stmt->execute([$review_id]);
        $_SESSION['success'] = 'Отзыв удалён';
    }
    redirect('reviews.php');
}
?>

<h2>Все отзывы</h2>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (count($reviews) > 0): ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Автор</th>
                    <th>Отзыв</th>
                    <th>Статус</th>
                    <th>Модератор</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                <tr>
                    <td><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></td>
                    <td><strong><?= e($review['nickname']) ?></strong></td>
                    <td><?= nl2br(e($review['content'])) ?></td>
                    <td>
                        <?php if ($review['is_approved']): ?>
                            <span style="color: #2e7d32;">✓ Одобрен</span>
                        <?php else: ?>
                            <span style="color: #c62828;">○ На модерации</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($review['moderated_by']): ?>
                            <?= e($review['moderator_name']) ?><br>
                            <small><?= date('d.m.Y', strtotime($review['moderated_at'])) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$review['is_approved']): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-success">✓ Одобрить</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                            <button type="submit" name="action" value="delete" class="btn btn-danger" 
                                    onclick="return confirm('Удалить отзыв?')">🗑️ Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>Пока нет отзывов</p>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>