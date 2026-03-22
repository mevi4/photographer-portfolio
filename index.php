<?php
require_once 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

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

// Генерируем пазл
$puzzle = generatePuzzleCaptcha();

// Проверяем, что данные пазла валидны
$puzzle_valid = isset($puzzle['bg']) && isset($puzzle['piece']) && !empty($puzzle['bg']) && !empty($puzzle['piece']);

// Если пазл невалиден, пишем в лог (для отладки)
if (!$puzzle_valid) {
    error_log('Puzzle generation failed. Check GD and folder permissions.');
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
                        alt="Фотограф за работой" class="photographer-photo">
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
                            <div class="project-item portfolio-item" data-images='<?= json_encode($all_photos) ?>'
                                data-first-image="<?= e($first_photo) ?>" data-title="<?= e($album['title']) ?>"
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
                    <form action="review_add.php" method="post" id="reviewForm">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <div class="form-group">
                            <input type="text" name="nickname" placeholder="Ваше имя" required>
                        </div>
                        <div class="form-group">
                            <textarea name="content" placeholder="Ваш отзыв" rows="4" required></textarea>
                        </div>
                        <div class="captcha-wrapper">
                            <div id="captcha-trigger" class="captcha-square">🔒</div>
                            <div id="captcha-success" class="captcha-success" style="display: none;">✅</div>
                        </div>
                        <input type="hidden" name="puzzle_solved" id="puzzle_solved" value="0">
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
                    <form action="contact.php" method="post" id="contactForm">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <div class="form-group">
                            <input type="tel" name="phone" id="phone" placeholder="+7 (999) 123-45-67" required>
                        </div>
                        <div class="captcha-wrapper">
                            <div id="captcha-trigger2" class="captcha-square">🔒</div>
                            <div id="captcha-success2" class="captcha-success" style="display: none;">✅</div>
                        </div>
                        <input type="hidden" name="puzzle_solved" id="puzzle_solved2" value="0">
                        <button type="submit" class="submit-btn">Отправить заявку</button>
                        <p class="form-note">Введите номер в любом формате: 89991234567, +7 999 123-45-67, 9991234567
                        </p>
                    </form>
                </div>
            </div>

            <!-- Блок капчи-пазл (модальное окно) -->
            <?php if ($puzzle_valid): ?>
                <div id="puzzle-modal" class="puzzle-modal" style="display: none;">
                    <div class="puzzle-captcha-block">
                        <div class="form-group">
                            <label>Соберите пазл: перетащите кусочек в серое отверстие</label>
                            <div id="puzzle-area" style="position: relative; display: inline-block; overflow: auto;">
                                <img src="<?= $puzzle['bg'] ?>" id="puzzle-bg" style="border: 1px solid #ddd;">
                                <div id="puzzle-piece"
                                    style="position: absolute; left: 0; top: 0; cursor: grab; z-index: 10;">
                                    <img src="<?= $puzzle['piece'] ?>"
                                        style="width: <?= $puzzle['pieceW'] ?>px; height: <?= $puzzle['pieceH'] ?>px;">
                                </div>
                            </div>
                            <div id="puzzle-status" class="puzzle-status"></div>
                            <button type="button" id="close-puzzle" class="close-puzzle-btn">✖ Закрыть</button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="error-message" style="margin: 20px 0;">⚠️ Ошибка генерации капчи...</div>
            <?php endif; ?>

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
        /* Стили для вкладок (оставляем как есть) */
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

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #c62828;
        }
    </style>

    <script>
        // Переключение вкладок
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        // Данные для превью
        const portfolioItems = document.querySelectorAll('.portfolio-item');
        const previewImage = document.getElementById('previewImage');
        const previewInfo = document.getElementById('previewInfo');

        let currentItemIndex = 0;
        let currentImageIndex = 0;
        let itemsWithImages = [];

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
                } catch (e) {
                    console.log('Ошибка парсинга изображений');
                }
            }
        });

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

        window.addEventListener('load', function () {
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

        portfolioItems.forEach(item => {
            item.addEventListener('mouseenter', function () {
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
                    } catch (e) { }
                }
            });
        });

        // Валидация телефона на клиенте
        document.querySelector('.contact-form form')?.addEventListener('submit', function (e) {
            const phoneInput = this.querySelector('input[name="phone"]');
            let phone = phoneInput.value.trim();
            let digits = phone.replace(/[^0-9]/g, '');
            if (digits.length < 10 || digits.length > 12) {
                e.preventDefault();
                alert('Введите корректный номер телефона (10-12 цифр)');
                phoneInput.focus();
                return false;
            }
            let firstDigit = digits[0];
            if (firstDigit !== '7' && firstDigit !== '8' && firstDigit !== '9') {
                e.preventDefault();
                alert('Номер должен начинаться с 7, 8 или 9');
                phoneInput.focus();
                return false;
            }
            return true;
        });

        // ============ Капча-пазл с модальным окном ============
        <?php if ($puzzle_valid): ?>
            const puzzleModal = document.getElementById('puzzle-modal');
            const closePuzzleBtn = document.getElementById('close-puzzle');
            const puzzleBg = document.getElementById('puzzle-bg');
            const puzzlePiece = document.getElementById('puzzle-piece');
            const puzzleStatus = document.getElementById('puzzle-status');
            const hiddenInputs = document.querySelectorAll('#puzzle_solved, #puzzle_solved2');
            const successIcons = document.querySelectorAll('.captcha-success');
            const triggerButtons = document.querySelectorAll('.captcha-square');
            const submitButtons = document.querySelectorAll('.submit-btn');
            const csrfToken = '<?= generateCSRFToken() ?>';

            let isPuzzleSolved = false;
            // --- Добавляем новый флаг ---
            let isCheckingSolution = false;
            // ----------------------------

            function showPuzzleModal() {
                // --- Проверяем флаг ---
                if (isPuzzleSolved || isCheckingSolution) return;
                // ----------------------
                puzzleModal.style.display = 'block';
                setTimeout(() => {
                    puzzlePiece.style.left = '0px';
                    puzzlePiece.style.top = '0px';
                }, 10);
            }

            function hidePuzzleModal() {
                puzzleModal.style.display = 'none';
            }

            // Блокировка/разблокировка кнопок отправки
            function setButtonsEnabled(enabled) {
                submitButtons.forEach(btn => btn.disabled = !enabled);
            }

            async function setSolved(value) {
                if (value == '1') {
                    if (isCheckingSolution) {
                        console.log('Проверка решения уже запущена.');
                        return;
                    }
                    isCheckingSolution = true;
                    console.log('Запуск проверки решения через AJAX...');
                    setButtonsEnabled(false); // блокируем на время AJAX
                    try {
                        const response = await fetch('verify_captcha.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'action=solve&csrf_token=' + encodeURIComponent(csrfToken)
                        });
                        const data = await response.json();
                        if (data.success) {
                            console.log('AJAX success: Сервер подтвердил решение.');
                            isPuzzleSolved = true;
                            hiddenInputs.forEach(input => input.value = '1');
                            puzzleStatus.innerHTML = '✓ Пазл собран!';
                            puzzleStatus.style.color = '#2e7d32';
                            triggerButtons.forEach(btn => btn.style.setProperty('display', 'none', 'important'));
                            successIcons.forEach(icon => icon.style.setProperty('display', 'block', 'important'));
                            hidePuzzleModal();
                            console.log('Пазл собран! Квадратики скрыты, галочки показаны.');
                            setButtonsEnabled(true); // разблокируем после успеха
                        } else {
                            console.warn('AJAX failed: Сервер не подтвердил решение.');
                            setButtonsEnabled(true);
                            alert('Ошибка капчи, попробуйте ещё раз.');
                        }
                    } catch (err) {
                        console.error('Ошибка отправки запроса:', err);
                        setButtonsEnabled(true);
                        alert('Ошибка соединения, попробуйте ещё раз.');
                    } finally {
                        isCheckingSolution = false;
                    }
                } else if (value == '0') {
                    if (isCheckingSolution) {
                        console.log('Сброс состояния игнорируется, так как идёт проверка решения.');
                        return;
                    }
                    console.log('Сброс состояния пазла.');
                    isPuzzleSolved = false;
                    hiddenInputs.forEach(input => input.value = '0');
                    puzzleStatus.innerHTML = 'Переместите фрагмент в серое отверстие';
                    puzzleStatus.style.color = '#666';
                    triggerButtons.forEach(btn => btn.style.setProperty('display', 'block', 'important'));
                    successIcons.forEach(icon => icon.style.setProperty('display', 'none', 'important'));
                    setButtonsEnabled(false); // кнопки заблокированы, пока пазл не собран
                }
            }


            // Координаты отверстия
            const holeX = <?= (int) $puzzle['holeX'] ?>;
            const holeY = <?= (int) $puzzle['holeY'] ?>;
            const pieceW = <?= (int) $puzzle['pieceW'] ?>;
            const pieceH = <?= (int) $puzzle['pieceH'] ?>;

            let isDragging = false, offsetX = 0, offsetY = 0;

            function getBgRect() {
                return puzzleBg.getBoundingClientRect();
            }

            // --- Изменяем функцию checkMatch ---
            function checkMatch() {
                // Не проверяем, если уже решено или идёт проверка
                if (isPuzzleSolved || isCheckingSolution) {
                    return;
                }
                const pieceRect = puzzlePiece.getBoundingClientRect();
                const bgRect = getBgRect();
                const leftRel = pieceRect.left - bgRect.left;
                const topRel = pieceRect.top - bgRect.top;
                const tolerance = 8; // Можно регулировать чувствительность
                const matched = Math.abs(leftRel - holeX) <= tolerance && Math.abs(topRel - holeY) <= tolerance;
                if (matched) {
                    console.log('Обнаружено совпадение! Пытаемся подтвердить решение...');
                    setSolved('1'); // Вызовется только если isPuzzleSolved = false и isCheckingSolution = false
                }
                // Не вызываем setSolved('0'), если !matched - пусть состояние остаётся как есть
                // setSolved('0') вызывается вручную (например, при открытии модального окна снова)
            }
            // ------------------------------------


            puzzlePiece.addEventListener('mousedown', (e) => {
                // --- Проверяем флаги ---
                if (isPuzzleSolved || isCheckingSolution) return;
                // -----------------------
                e.preventDefault();
                isDragging = true;
                const pieceRect = puzzlePiece.getBoundingClientRect();
                const bgRect = getBgRect();
                offsetX = e.clientX - pieceRect.left;
                offsetY = e.clientY - pieceRect.top;
                puzzlePiece.style.cursor = 'grabbing';
            });

            window.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                const bgRect = getBgRect();
                let newLeft = e.clientX - offsetX - bgRect.left;
                let newTop = e.clientY - offsetY - bgRect.top;
                newLeft = Math.max(0, Math.min(newLeft, bgRect.width - pieceW));
                newTop = Math.max(0, Math.min(newTop, bgRect.height - pieceH));
                puzzlePiece.style.left = newLeft + 'px';
                puzzlePiece.style.top = newTop + 'px';
                // Вызываем проверку только во время перетаскивания, если не решено и не проверяется
                // УБРАНО: if (!isPuzzleSolved && !isCheckingSolution) {
                // УБРАНО:      checkMatch();
                // УБРАНО: }
                // ОСТАВЛЯЕМ: вызов checkMatch только при mouseup, чтобы избежать флуда AJAX
            });

            window.addEventListener('mouseup', () => {
                if (isDragging) {
                    isDragging = false;
                    puzzlePiece.style.cursor = 'grab';
                    // Вызываем проверку при отпускании, если не решено и не проверяется
                    // УБРАНО: if (!isPuzzleSolved && !isCheckingSolution) {
                    checkMatch(); // Только при отпускании
                    // УБРАНО: }
                }
            });

            triggerButtons.forEach(btn => btn.addEventListener('click', showPuzzleModal));
            closePuzzleBtn.addEventListener('click', hidePuzzleModal);
            // Инициализируем начальное состояние
            setSolved('0');

            const originalShowTab = window.showTab;
            window.showTab = function (tabName) {
                originalShowTab(tabName);
                if (puzzleModal.style.display === 'block') {
                    hidePuzzleModal();
                }
            };
        <?php endif; ?>
    </script>
</body>

</html>