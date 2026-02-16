<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/models/VeteranModel.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /');
    exit;
}

$veteranModel = new VeteranModel();
$veteran = $veteranModel->getById($id);

if (!$veteran || $veteran['status'] !== 'approved') {
    header('HTTP/1.0 404 Not Found');
    die('Ветеран не найден');
}

// Увеличиваем счётчик просмотров
$veteranModel->incrementViews($id);

// Парсим фото
$photos = !empty($veteran['photos']) ? json_decode($veteran['photos'], true) : [];
?\>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($veteran['last_name'] . ' ' . $veteran['first_name']) ?\u003e | <?= e(SITE_NAME) ?\u003e</title>
    <link href="https://fonts.googleapis.com/css2?family=PT+Serif:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .veteran-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);
            padding: 60px 0;
            text-align: center;
        }
        
        .veteran-name {
            font-family: var(--font-serif);
            font-size: 2.5rem;
            color: var(--color-primary);
            margin-bottom: 15px;
        }
        
        .veteran-years {
            font-size: 1.3rem;
            color: var(--color-secondary);
        }
        
        .veteran-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 40px;
            padding: 40px 0;
        }
        
        .veteran-sidebar {
            background: rgba(255,255,255,0.03);
            border-radius: 15px;
            padding: 25px;
            height: fit-content;
        }
        
        .veteran-main-photo {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .veteran-info-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        }
        
        .veteran-info-item:last-child {
            border-bottom: none;
        }
        
        .veteran-info-label {
            font-size: 0.85rem;
            color: var(--color-text-muted);
            margin-bottom: 5px;
        }
        
        .veteran-info-value {
            font-size: 1rem;
            color: var(--color-text);
        }
        
        .veteran-body h2 {
            font-family: var(--font-serif);
            color: var(--color-primary);
            margin: 30px 0 15px;
        }
        
        .veteran-body p {
            margin-bottom: 15px;
            line-height: 1.8;
        }
        
        .awards-list {
            background: rgba(212, 175, 55, 0.1);
            border-left: 4px solid var(--color-primary);
            padding: 20px;
            border-radius: 0 10px 10px 0;
        }
        
        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .photo-gallery img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .photo-gallery img:hover {
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .veteran-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="main-nav">
        <div class="nav-container">
            <a href="/" class="nav-logo">← Назад</a>
        </div>
    </nav>

    <header class="veteran-header">
        <div class="container">
            <h1 class="veteran-name">
                <?= e($veteran['last_name'] . ' ' . $veteran['first_name'] . ' ' . $veteran['patronymic']) ?\u003e
            </h1>
            <p class="veteran-years">
                <?= $veteran['birth_year'] ?\u003e — <?= $veteran['death_year'] ?? 'н.в.' ?\u003e
            </p>
        </div>
    </header>

    <main class="container veteran-content">
        <aside class="veteran-sidebar">
            <?php if (!empty($veteran['photo_main'])): ?\u003e
            <img src="/uploads/photos/<?= e($veteran['photo_main']) ?\u003e" 
                 alt="<?= e($veteran['last_name']) ?\u003e"
                 class="veteran-main-photo">
            <?php else: ?\u003e
            <div style="text-align: center; padding: 40px; background: rgba(0,0,0,0.3); border-radius: 10px;">
                <div style="font-size: 4rem;">🎖️</div>
            </div>
            <?php endif; ?\u003e
            
            <?php if ($veteran['rank']): ?\u003e
            <div class="veteran-info-item">
                <div class="veteran-info-label">Звание</div>
                <div class="veteran-info-value"><?= e($veteran['rank']) ?\u003e</div>
            </div>
            <?php endif; ?\u003e
            
            <?php if ($veteran['settlement']): ?\u003e
            <div class="veteran-info-item">
                <div class="veteran-info-label">Населённый пункт</div>
                <div class="veteran-info-value"><?= e($veteran['settlement']) ?\u003e</div>
            </div>
            <?php endif; ?\u003e
            
            <div class="veteran-info-item">
                <div class="veteran-info-label">Просмотров</div>
                <div class="veteran-info-value"><?= number_format($veteran['views_count']) ?\u003e</div>
            </div>
        </aside>

        <article class="veteran-body">
            <?php if ($veteran['awards']): ?\u003e
            <h2>🎖️ Награды</h2>
            <div class="awards-list">
                <?= nl2br(e($veteran['awards'])) ?\u003e
            </div>
            <?php endif; ?\u003e
            
            <?php if ($veteran['front_path']): ?\u003e
            <h2>⚔️ Фронтовой путь</h2>
            <p><?= nl2br(e($veteran['front_path'])) ?\u003e</p>
            <?php endif; ?\u003e
            
            <?php if ($veteran['biography']): ?\u003e
            <h2>📖 Биография</h2>
            <div class="biography-text">
                <?= nl2br(e($veteran['biography'])) ?\u003e
            </div>
            <?php endif; ?\u003e
            
            <?php if (!empty($photos)): ?\u003e
            <h2>📸 Фотографии</h2>
            <div class="photo-gallery">
                <?php foreach ($photos as $photo): ?\u003e
                <img src="/uploads/thumbs/<?= e($photo) ?\u003e" 
                     alt=""
                     onclick="openModal('/uploads/photos/<?= e($photo) ?\u003e')">
                <?php endforeach; ?\u003e
            </div>
            <?php endif; ?\u003e
        </article>
    </main>

    <footer class="footer">
        <div class="container">
            <p>© 2026 <?= e(SITE_NAME) ?\u003e</p>
        </div>
    </footer>

    <script>
        function openModal(src) {
            // Здесь можно добавить модальное окно для просмотра фото
            window.open(src, '_blank');
        }
    </script>
</body>
</html>
