<?php
require_once 'includes/header.php';

$album_id = isset($_GET['album_id']) ? (int)$_GET['album_id'] : 0;
if (!$album_id) {
    redirect('albums.php');
}

$stmt = $pdo->prepare("SELECT * FROM album WHERE id = ?");
$stmt->execute([$album_id]);
$album = $stmt->fetch();
if (!$album) {
    redirect('albums.php');
}

$stmt = $pdo->prepare("
    SELECT p.*, ap.sort_order 
    FROM photo p 
    JOIN album_photo ap ON p.id = ap.photo_id 
    WHERE ap.album_id = ? 
    ORDER BY ap.sort_order ASC, p.uploaded_at DESC
");
$stmt->execute([$album_id]);
$photos = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $targetDir = '../uploads/albums/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['photo']['name']);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        
        $allowTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
                $title = trim($_POST['title'] ?? '');
                $stmt = $pdo->prepare("INSERT INTO photo (file_path, title, uploaded_by) VALUES (?, ?, ?)");
                $stmt->execute(['uploads/albums/' . $fileName, $title, $_SESSION['user_id']]);
                $photo_id = $pdo->lastInsertId();
                
                $stmt = $pdo->prepare("INSERT INTO album_photo (album_id, photo_id, sort_order) VALUES (?, ?, ?)");
                $stmt->execute([$album_id, $photo_id, 0]);
                
                $_SESSION['success'] = 'Фото загружено';
            }
        } else {
            $_SESSION['error'] = 'Недопустимый формат файла';
        }
    }
    redirect("photos.php?album_id=$album_id");
}

if (isset($_GET['detach'])) {
    $photo_id = (int)$_GET['detach'];
    $stmt = $pdo->prepare("DELETE FROM album_photo WHERE album_id = ? AND photo_id = ?");
    $stmt->execute([$album_id, $photo_id]);
    $_SESSION['success'] = 'Фото удалено из альбома';
    redirect("photos.php?album_id=$album_id");
}
?>

<h2>Фото в альбоме "<?= e($album['title']) ?>"</h2>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="card">
    <h3>Загрузить новое фото</h3>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="photo">Выберите фото</label>
            <input type="file" name="photo" id="photo" accept="image/*" required>
        </div>
        <div class="form-group">
            <label for="title">Название (необязательно)</label>
            <input type="text" name="title" id="title">
        </div>
        <button type="submit" name="upload" class="btn btn-primary">Загрузить</button>
        <a href="albums.php" class="btn">← Назад</a>
    </form>
</div>

<?php if (count($photos) > 0): ?>
    <div class="photo-grid">
        <?php foreach ($photos as $photo): ?>
            <div class="photo-card">
                <img src="../<?= e($photo['file_path']) ?>" alt="<?= e($photo['title']) ?>">
                <div class="photo-card-body">
                    <div class="photo-card-title"><?= e($photo['title']) ?: 'Без названия' ?></div>
                    <div class="photo-card-actions">
                        <a href="?detach=<?= $photo['id'] ?>&album_id=<?= $album_id ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Удалить фото из альбома?')">Удалить</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>В альбоме нет фотографий</p>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>