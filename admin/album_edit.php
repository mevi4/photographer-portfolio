<?php
require_once 'includes/header.php';

$album_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$album_id) {
    redirect('albums.php');
}

$stmt = $pdo->prepare("SELECT * FROM album WHERE id = ?");
$stmt->execute([$album_id]);
$album = $stmt->fetch();

if (!$album) {
    redirect('albums.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    
    if (!empty($title)) {
        $stmt = $pdo->prepare("UPDATE album SET title = ?, description = ? WHERE id = ?");
        $stmt->execute([$title, $description, $album_id]);
        $_SESSION['success'] = 'Альбом обновлён';
        redirect('albums.php');
    } else {
        $_SESSION['error'] = 'Название альбома обязательно';
    }
}
?>

<h2>Редактирование альбома</h2>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error"><?= e($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="card">
    <form method="post">
        <div class="form-group">
            <label for="title">Название альбома</label>
            <input type="text" name="title" id="title" value="<?= e($album['title']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description">Описание</label>
            <textarea name="description" id="description" rows="5"><?= e($album['description']) ?></textarea>
        </div>
        
        <button type="submit" name="update" class="btn btn-primary">Сохранить изменения</button>
        <a href="albums.php" class="btn">Отмена</a>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>