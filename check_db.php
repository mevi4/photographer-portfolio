<?php
require_once 'config.php';

echo "<h2>Проверка базы данных</h2>";

// Проверяем альбомы
$stmt = $pdo->query("SELECT * FROM album");
$albums = $stmt->fetchAll();
echo "<h3>Альбомы (" . count($albums) . "):</h3>";
echo "<pre>";
print_r($albums);
echo "</pre>";

// Проверяем фото
$stmt = $pdo->query("SELECT * FROM photo");
$photos = $stmt->fetchAll();
echo "<h3>Фото (" . count($photos) . "):</h3>";
echo "<pre>";
print_r($photos);
echo "</pre>";

// Проверяем связи альбом-фото
$stmt = $pdo->query("SELECT * FROM album_photo");
$links = $stmt->fetchAll();
echo "<h3>Связи альбом-фото (" . count($links) . "):</h3>";
echo "<pre>";
print_r($links);
echo "</pre>";
?>