<?php
// Главная страница музея - версия для теста git pull
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/models/VeteranModel.php';

$veteranModel = new VeteranModel();

// Параметры поиска
$page = max(1, intval($_GET['page'] ?? 1));
$filters = [
    'search' => $_GET['search'] ?? '',
    'settlement' => $_GET['settlement'] ?? ''
];

// Получаем данные
$result = $veteranModel->getAll($filters, $page);
$settlements = $veteranModel->getSettlements();
$stats = $veteranModel->getStats();
?\>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(SITE_NAME) ?\u003e | Герои Олёкминского района</title>
    <link href="https://fonts.googleapis.com/css2?family=PT+Serif:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Герой-секция -->
    <header class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="eternal-flame">🔥</div>
            <h1>Память Олёкминского края</h1>
            <p class="subtitle">Виртуальный музей участников Великой Отечественной войны</p>
            <p class="years">1941 — 1945</p>
            
            <div class="stats">
                <div class="stat-item">
                    <span class="stat-number"><?= number_format($stats['total']) ?\u003e</span>
                    <span class="stat-label">ветеранов в базе</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= number_format($stats['with_photo']) ?\u003e</span>
                    <span class="stat-label">с фотографиями</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Навигация -->
    <nav class="main-nav">
        <div class="nav-container">
            <a href="/" class="nav-logo">ПАМЯТЬ</a>
            <ul class="nav-menu">
                <li><a href="/">Главная</a></li>
                <li><a href="/bessmertny-pol.php">🎖️ Бессмертный полк</a></li>
                <li><a href="/add.php">➕ Добавить героя</a></li>
                <li><a href="/about.php">О проекте</a></li>
            </ul>
            <button class="nav-toggle">☰</button>
        </div>
    </nav>

    <!-- Поиск и фильтры -->
    <section class="search-section">
        <div class="container">
            <h2>🔍 Найти ветерана</h2>
            
            <form class="search-form" method="get">
                <div class="search-row">
                    <input type="text" 
                           name="search" 
                           placeholder="Введите фамилию..." 
                           value="<?= e($filters['search']) ?\u003e"
                           class="search-input">
                    
003cbutton type="submit" class="btn btn-primary">Найти</button>
                </div>
                
                <?php if (!empty($settlements)): ?\u003e
                <div class="filter-row">
                    <select name="settlement" class="filter-select">
                        <option value="">Все населённые пункты</option>
                        <?php foreach ($settlements as $settlement): ?\u003e
                        <option value="<?= e($settlement) ?\u003e" 
                                <?= $filters['settlement'] === $settlement ? 'selected' : '' ?\u003e>
                            <?= e($settlement) ?\u003e
                        </option>
                        <?php endforeach; ?\u003e
                    </select>
                </div>
                <?php endif; ?\u003e
            </form>
            
            <?php if (!empty($filters['search']) || !empty($filters['settlement'])): ?\u003e
            <p class="search-results-info">
                Найдено: <strong><?= $result['total'] ?\u003e</strong> <?= plural($result['total'], ['ветеран', 'ветерана', 'ветеранов']) ?\u003e
                <a href="/" class="clear-link">Сбросить</a>
            </p>
            <?php endif; ?\u003e
        </div>
    </section>

    <!-- Список ветеранов -->
    <section class="heroes" id="heroes">
        <div class="container">
            <h2>🎖️ Герои Олёкминского района</h2>
            
            <?php if (empty($result['items'])): ?\u003e
            <div class="empty-state">
                <p>Ничего не найдено. Попробуйте изменить параметры поиска.</p>
                <a href="/add.php" class="btn btn-outline">Добавить информацию</a>
            </div>
            <?php else: ?\u003e
            
            <div class="heroes-grid">
                <?php foreach ($result['items'] as $veteran): ?\u003e
                <article class="hero-card">
                    <a href="/veteran.php?id=<?= $veteran['id'] ?\u003e" class="hero-card-link">
                        <div class="hero-photo">
                            <?php if (!empty($veteran['photo_main'])): ?\u003e
                            <img src="/uploads/thumbs/<?= e($veteran['photo_main']) ?\u003e" 
                                 alt="<?= e($veteran['last_name']) ?\u003e">
                            <?php else: ?\u003e
                            <div class="hero-photo-placeholder">🎖️</div>
                            <?php endif; ?\u003e
                        </div>
                        <div class="hero-info">
                            <h3 class="hero-name">
                                <?= e($veteran['last_name'] . ' ' . $veteran['first_name']) ?\u003e
                            </h3>
                            <?php if ($veteran['patronymic']): ?\u003e
                            <p class="hero-patronymic"><?= e($veteran['patronymic']) ?\u003e</p>
                            <?php endif; ?\u003e
                            
                            <?php if ($veteran['years']): ?\u003e
                            <p class="hero-years"><?= e($veteran['years']) ?\u003e</p>
                            <?php elseif ($veteran['birth_year']): ?\u003e
                            <p class="hero-years">
                                <?= $veteran['birth_year'] ?\u003e — <?= $veteran['death_year'] ?? 'н.в.' ?\u003e
                            </p>
                            <?php endif; ?\u003e
                            
                            <?php if ($veteran['rank']): ?\u003e
                            <p class="hero-rank"><?= e($veteran['rank']) ?\u003e</p>
                            <?php endif; ?\u003e
                            
                            <?php if ($veteran['settlement']): ?\u003e
                            <p class="hero-settlement">📍 <?= e($veteran['settlement']) ?\u003e</p>
                            <?php endif; ?\u003e
                        </div>
                    </a>
                </article>
                <?php endforeach; ?\u003e
            </div>

            <!-- Пагинация -->
            <?php if ($result['pages'] > 1): ?\u003e
            <nav class="pagination">
                <?php if ($page > 1): ?\u003e
                <a href="?page=<?= $page-1 ?\u003e&search=<?= urlencode($filters['search']) ?\u003e&settlement=<?= urlencode($filters['settlement']) ?\u003e" 
                   class="btn btn-outline">← Назад</a>
                <?php endif; ?\u003e
                
                <span class="page-info">Страница <?= $page ?\u003e из <?= $result['pages'] ?\u003e</span>
                
                <?php if ($page < $result['pages']): ?\u003e
                <a href="?page=<?= $page+1 ?\u003e&search=<?= urlencode($filters['search']) ?\u003e&settlement=<?= urlencode($filters['settlement']) ?\u003e" 
                   class="btn btn-outline">Вперёд →</a>
                <?php endif; ?\u003e
            </nav>
            <?php endif; ?\u003e
            
            <?php endif; ?\u003e
        </div>
    </section>

    <!-- Призыв добавить -->
    <section class="cta-section">
        <div class="container">
            <h2>Есть информация о ветеране?</h2>
            <p>Помогите сохранить память о подвиге наших земляков. Добавьте биографию, фотографии, документы.</p>
            <a href="/add.php" class="btn btn-primary btn-large">➕ Добавить героя</a>
        </div>
    </section>

    <!-- Футер -->
    <footer class="footer">
        <div class="container">
            <p>© 2026 <?= e(SITE_NAME) ?\u003e</p>
            <p class="eternal-memory">Вечная память героям! 🔥</p>
        </div>
    </footer>

    <script src="/assets/js/main.js"></script>
</body>
</html>
<?php
// Вспомогательная функция для склонения числительных
function plural($n, $forms) {
    return $n % 10 == 1 && $n % 100 != 11 ? $forms[0] : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[1] : $forms[2]);
}
?\>
