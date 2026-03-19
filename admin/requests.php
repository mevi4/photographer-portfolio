<?php
require_once 'includes/header.php';

$stmt = $pdo->query("
    SELECT cr.*, u.username as processor 
    FROM contact_request cr
    LEFT JOIN user u ON cr.processed_by = u.id
    ORDER BY cr.created_at DESC
");
$requests = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_processed'])) {
    $id = (int)$_POST['request_id'];
    $stmt = $pdo->prepare("UPDATE contact_request SET is_processed = 1, processed_by = ?, processed_at = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    $_SESSION['success'] = 'Заявка отмечена как обработанная';
    redirect('requests.php');
}
?>

<h2>Заявки на съёмку</h2>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (count($requests) > 0): ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Телефон</th>
                    <th>Статус</th>
                    <th>Обработал</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                <tr>
                    <td><?= date('d.m.Y H:i', strtotime($req['created_at'])) ?></td>
                    <td><strong><?= e($req['phone_number']) ?></strong></td>
                    <td>
                        <?php if ($req['is_processed']): ?>
                            <span style="color: #2e7d32;">✓ Обработано</span>
                        <?php else: ?>
                            <span style="color: #c62828;">○ Новое</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($req['processed_by']): ?>
                            <?= e($req['processor']) ?><br>
                            <small><?= date('d.m.Y', strtotime($req['processed_at'])) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$req['is_processed']): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                <button type="submit" name="mark_processed" class="btn btn-success">✓ Отметить</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>Пока нет заявок</p>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>