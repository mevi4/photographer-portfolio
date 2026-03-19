<?php
require_once 'config.php';

// Получаем все альбомы с их фотографиями
$stmt = $pdo->query("
    SELECT a.*, 
           GROUP_CONCAT(p.file_path SEPARATOR '|') as photos,
           GROUP_CONCAT(p.title SEPARATOR '|') as photo_titles
    FROM album a
    LEFT JOIN album_photo ap ON a.id = ap.album_id
    LEFT JOIN photo p ON ap.photo_id = p.id
    GROUP BY a.id
    ORDER BY a.created_at DESC
");
$albums = $stmt->fetchAll();

// Получаем одобренные отзывы
$stmt = $pdo->prepare("SELECT * FROM review WHERE is_approved = 1 ORDER BY created_at DESC");
$stmt->execute();
$reviews = $stmt->fetchAll();

// Получаем первое фото для превью (если есть)
$preview_image = 'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?w=600';
if (!empty($albums) && !empty($albums[0]['photos'])) {
    $first_photos = explode('|', $albums[0]['photos']);
    $preview_image = $first_photos[0];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Иванна Иванова | Фотограф</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Левая часть с контентом -->
        <main class="content">
           <div class="header">
    <div class="photographer-info">
        <div class="photographer-text">
            <h1>Иванна Иванова</h1>
            <div class="meta-info">
                <span class="location">Москва, Россия</span>
                <span class="separator">|</span>
                <span class="profession">Фотограф</span>
                <span class="separator">|</span>
                <span class="experience">10 лет</span>
            </div>
        </div>
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRcK1F2-aOQRLYcLLrk0SO-EgoZnLpLKVTk3A&s" 
             alt="Фотограф за работой" 
             class="photographer-photo">
    </div>
</div>

            <!-- Навигационные вкладки -->
            <div class="tabs">
                <button class="tab-button active" onclick="showTab('portfolio')">Портфолио</button>
                <button class="tab-button" onclick="showTab('reviews')">Отзывы</button>
                <button class="tab-button" onclick="showTab('contact')">Контакты</button>
            </div>

            <!-- Содержимое вкладок -->
            <div id="portfolio" class="tab-content active">
                <div class="projects-list" id="projectsList">
                    <?php if (count($albums) > 0): ?>
                        <?php foreach ($albums as $index => $album): 
                            $first_photo = '';
                            $all_photos = [];
                            
                            if (!empty($album['photos'])) {
                                $all_photos = explode('|', $album['photos']);
                                $first_photo = $all_photos[0];
                            }
                        ?>
                            <div class="project-item portfolio-item" 
                                 data-images='<?= json_encode($all_photos) ?>'
                                 data-first-image="<?= e($first_photo) ?>"
                                 data-title="<?= e($album['title']) ?>"
                                 data-description="<?= e($album['description'] ?? '') ?>">
                                <span class="project-number"><?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></span>
                                <span class="project-name"><?= e($album['title']) ?></span>
                                <span class="project-count"><?= count($all_photos) ?> фото</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="project-item no-albums">
                            <span class="project-number">--</span>
                            <span class="project-name">Нет альбомов</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div id="reviews" class="tab-content">
                <div class="reviews-list">
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <p class="review-text">"<?= nl2br(e($review['content'])) ?>"</p>
                                <div class="review-footer">
                                    <span class="review-author">— <?= e($review['nickname']) ?></span>
                                    <span class="review-date"><?= date('d.m.Y', strtotime($review['created_at'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-reviews">Пока нет отзывов. Будьте первым!</p>
                    <?php endif; ?>
                </div>
                
                <!-- Форма добавления отзыва -->
                <div class="add-review-form">
                    <h3>Оставить отзыв</h3>
                    <form action="review_add.php" method="post">
                        <div class="form-group">
                            <input type="text" name="nickname" placeholder="Ваше имя" required>
                        </div>
                        <div class="form-group">
                            <textarea name="content" placeholder="Ваш отзыв" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <input type="text" name="captcha" placeholder="Сколько будет 2 + 3?" required>
                        </div>
                        <button type="submit" class="submit-btn">Отправить отзыв</button>
                    </form>
                </div>
            </div>

            <div id="contact" class="tab-content">
                <div class="contact-info">
                    <h3>Контакты</h3>
                    <p>📞 Телефон: +7 (999) 123-45-67</p>
                    <p>📧 Email: ivanna.i@mail.com</p>
                    <p>📍 Город: Москва</p>
                </div>

                <div class="contact-form">
                    <h3>Заказать съёмку</h3>
                    <form action="contact.php" method="post">
                        <div class="form-group">
                            <input type="tel" name="phone" placeholder="Ваш номер телефона" required>
                        </div>
                        <button type="submit" class="submit-btn">Отправить заявку</button>
                    </form>
                </div>
            </div>

            <!-- Кнопка входа для администратора -->
            <div class="admin-button-container">
                <a href="admin/login.php" class="admin-login-button" title="Вход в админ-панель">
                    <span class="admin-icon">👤</span>
                    <span class="admin-text">Админ</span>
                </a>
            </div>
        </main>

        <!-- Правая часть с превью -->
        <aside class="preview" id="preview">
            <div class="preview-container">
                <img src="<?= e($preview_image) ?>" alt="Preview" class="preview-image" id="previewImage">
                <div class="preview-overlay">
                    <div class="preview-info" id="previewInfo"></div>
                </div>
            </div>
        </aside>
    </div>

    <style>
    /* Стили для вкладок */
    .tabs {
        display: flex;
        gap: 20px;
        margin: 30px 0 20px;
        border-bottom: 1px solid #eaeaea;
        padding-bottom: 10px;
    }

    .tab-button {
        background: none;
        border: none;
        font-size: 16px;
        font-weight: 500;
        color: #999;
        cursor: pointer;
        padding: 5px 0;
        position: relative;
        transition: color 0.3s;
    }

    .tab-button:hover {
        color: #000;
    }

    .tab-button.active {
        color: #000;
    }

    .tab-button.active::after {
        content: '';
        position: absolute;
        bottom: -11px;
        left: 0;
        width: 100%;
        height: 2px;
        background: #000;
    }

    .tab-content {
        display: none;
        flex: 1;
        overflow-y: auto;
    }

    .tab-content.active {
        display: block;
    }

    .project-count {
        margin-left: auto;
        font-size: 12px;
        color: #999;
    }

    .reviews-list {
        max-height: 400px;
        overflow-y: auto;
        padding-right: 10px;
        margin-bottom: 30px;
    }

    .add-review-form {
        background: #f8f8f8;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }

    .add-review-form h3 {
        font-size: 18px;
        margin-bottom: 15px;
        color: #333;
    }

    .contact-info {
        margin-bottom: 30px;
    }

    .contact-info h3 {
        font-size: 18px;
        margin-bottom: 15px;
        color: #333;
    }

    .contact-info p {
        margin-bottom: 10px;
        color: #666;
    }
    </style>

    <script>
    // Переключение вкладок
    function showTab(tabName) {
        // Скрываем все вкладки
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Убираем активный класс у всех кнопок
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Показываем выбранную вкладку
        document.getElementById(tabName).classList.add('active');
        
        // Активируем кнопку
        event.target.classList.add('active');
    }

    // Данные для превью
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    const previewImage = document.getElementById('previewImage');
    const previewInfo = document.getElementById('previewInfo');

    // Переменные для анимации
    let currentItemIndex = 0;
    let currentImageIndex = 0;
    let itemsWithImages = [];

    // Собираем все элементы, у которых есть изображения
    portfolioItems.forEach(item => {
        const images = item.dataset.images;
        if (images && images !== '[]' && images !== '') {
            try {
                const parsedImages = JSON.parse(images);
                if (parsedImages.length > 0) {
                    itemsWithImages.push({
                        element: item,
                        images: parsedImages,
                        title: item.dataset.title,
                        description: item.dataset.description
                    });
                }
            } catch(e) {
                console.log('Ошибка парсинга изображений');
            }
        }
    });

    // Функция для показа следующего изображения
    function showNextImage() {
        if (itemsWithImages.length === 0) return;
        
        const currentItem = itemsWithImages[currentItemIndex];
        const images = currentItem.images;
        
        if (images && images.length > 0) {
            currentImageIndex = (currentImageIndex + 1) % images.length;
            
            previewImage.style.opacity = '0.5';
            setTimeout(() => {
                previewImage.src = images[currentImageIndex];
                previewImage.style.opacity = '1';
            }, 200);
            
            let infoHtml = `<h3>${currentItem.title}</h3>`;
            if (currentItem.description) {
                infoHtml += `<p>${currentItem.description}</p>`;
            }
            previewInfo.innerHTML = infoHtml;
        }
    }

    // Запускаем автоматическую анимацию
    window.addEventListener('load', function() {
        if (itemsWithImages.length > 0) {
            const firstItem = itemsWithImages[0];
            if (firstItem.images.length > 0) {
                previewImage.src = firstItem.images[0];
                let infoHtml = `<h3>${firstItem.title}</h3>`;
                if (firstItem.description) {
                    infoHtml += `<p>${firstItem.description}</p>`;
                }
                previewInfo.innerHTML = infoHtml;
            }
            
            setInterval(showNextImage, 3000);
        }
    });

    // При наведении на альбом
    portfolioItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            const images = this.dataset.images;
            const title = this.dataset.title;
            const description = this.dataset.description;
            
            if (images && images !== '[]') {
                try {
                    const parsedImages = JSON.parse(images);
                    if (parsedImages.length > 0) {
                        const index = itemsWithImages.findIndex(i => i.title === title);
                        if (index !== -1) {
                            currentItemIndex = index;
                            currentImageIndex = 0;
                        }
                        
                        previewImage.style.opacity = '0.5';
                        setTimeout(() => {
                            previewImage.src = parsedImages[0];
                            previewImage.style.opacity = '1';
                        }, 200);
                        
                        let infoHtml = `<h3>${title}</h3>`;
                        if (description) {
                            infoHtml += `<p>${description}</p>`;
                        }
                        previewInfo.innerHTML = infoHtml;
                    }
                } catch(e) {}
            }
        });
    });
    </script>
</body>
</html>