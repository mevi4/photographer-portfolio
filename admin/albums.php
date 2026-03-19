<?php
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_album'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    
    if (!empty($title)) {
        $stmt = $pdo->prepare("INSERT INTO album (title, description, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $_SESSION['user_id']]);
        $_SESSION['success'] = 'Альбом создан';
        redirect('albums.php');
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("DELETE FROM album_photo WHERE album_id = ?");
    $stmt->execute([$id]);
    
    $stmt = $pdo->prepare("DELETE FROM album WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['success'] = 'Альбом удалён';
    redirect('albums.php');
}

$stmt = $pdo->query("
    SELECT a.*, u.username as creator, 
           COUNT(DISTINCT ap.photo_id) as photos_count
    FROM album a
    LEFT JOIN user u ON a.created_by = u.id
    LEFT JOIN album_photo ap ON a.id = ap.album_id
    GROUP BY a.id
    ORDER BY a.created_at DESC
");
$albums = $stmt->fetchAll();
?>

<h2>Альбомы</h2>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="card">
    <h3>Создать новый альбом</h3>
    <form method="post">
        <div class="form-group">
            <label for="title">Название альбома</label>
            <input type="text" name="title" id="title" required>
        </div>
        <div class="form-group">
            <label for="description">Описание (необязательно)</label>
            <textarea name="description" id="description" rows="3"></textarea>
        </div>
        <button type="submit" name="create_album" class="btn btn-primary">Создать альбом</button>
    </form>
</div>

<?php if (count($albums) > 0): ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Фото</th>
                    <th>Дата</th>
                    <th>Создатель</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($albums as $album): ?>
                <tr>
                    <td><strong><?= e($album['title']) ?></strong></td>
                    <td><?= e($album['description']) ?: '—' ?></td>
                    <td><?= $album['photos_count'] ?> шт.</td>
                    <td><?= date('d.m.Y', strtotime($album['created_at'])) ?></td>
                    <td><?= e($album['creator']) ?></td>
                    <td>
                        <a href="photos.php?album_id=<?= $album['id'] ?>" class="btn btn-primary">📸 Фото</a>
                        <a href="album_edit.php?id=<?= $album['id'] ?>" class="btn btn-primary">✏️ Ред.</a>
                        <a href="?delete=<?= $album['id'] ?>" class="btn btn-danger" 
                           onclick="return confirm('Удалить альбом?')">🗑️ Удалить</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>Нет альбомов. Создайте первый!</p>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>