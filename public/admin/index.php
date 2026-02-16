<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/models/VeteranModel.php';

requireAdmin(); // Проверяем авторизацию

$veteranModel = new VeteranModel();
$message = '';

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    
    if ($action === 'approve' && $id) {
        $veteranModel->setStatus($id, 'approved', $_SESSION['admin_id']);
        $message = '✅ Ветеран одобрен';
    } elseif ($action === 'reject' && $id) {
        $veteranModel->setStatus($id, 'rejected', $_SESSION['admin_id']);
        $message = '❌ Ветеран отклонён';
    } elseif ($action === 'delete' && $id) {
        // Удаление (мягкое или полное)
        $db = getDB();
        $stmt = $db->prepare("UPDATE veterans SET status = 'deleted' WHERE id = ?");
        $stmt->execute([$id]);
        $message = '🗑️ Запись удалена';
    }
}

// Получаем данные для таблиц
$pending = $veteranModel->getPending();
$approved = $veteranModel->getAll(['search' => '', 'settlement' => ''], 1, 100);
$stats = $veteranModel->getStats();
?\>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель | <?= e(SITE_NAME) ?\u003e</title>
    <link href="https://fonts.googleapis.com/css2?family=PT+Serif:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #d4af37;
            --secondary: #8b0000;
            --bg: #0d0d0d;
            --bg-light: #1a1a2e;
            --text: #e0e0e0;
            --text-muted: #888;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Roboto', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        .admin-header {
            background: linear-gradient(135deg, var(--bg-light) 0%, var(--bg) 100%);
            padding: 20px 30px;
            border-bottom: 3px solid var(--primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h1 {
            font-family: 'PT Serif', serif;
            color: var(--primary);
        }

        .admin-nav {
            display: flex;
            gap: 20px;
        }

        .admin-nav a {
            color: var(--text);
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 20px;
            transition: all 0.3s;
        }

        .admin-nav a:hover, .admin-nav a.active {
            background: var(--primary);
            color: var(--bg);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        /* Статистика */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--bg-light);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid rgba(212, 175, 55, 0.2);
        }

        .stat-card h3 {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary);
        }

        .stat-card.pending { border-color: var(--secondary); }
        .stat-card.pending .number { color: var(--secondary); }

        /* Секции */
        .section {
            background: var(--bg-light);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .section h2 {
            font-family: 'PT Serif', serif;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge {
            background: var(--secondary);
            color: white;
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 0.8rem;
        }

        /* Таблица */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .data-table th {
            color: var(--primary);
            font-weight: 500;
        }

        .data-table tr:hover {
            background: rgba(255,255,255,0.03);
        }

        .veteran-name {
            color: var(--primary);
            font-weight: 500;
        }

        .veteran-years {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Кнопки действий */
        .actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 20px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-approve {
            background: #2e8b57;
            color: white;
        }

        .btn-approve:hover {
            background: #3da66a;
        }

        .btn-reject {
            background: #8b0000;
            color: white;
        }

        .btn-reject:hover {
            background: #a00000;
        }

        .btn-view {
            background: rgba(212, 175, 55, 0.2);
            color: var(--primary);
            text-decoration: none;
        }

        .btn-view:hover {
            background: var(--primary);
            color: var(--bg);
        }

        .btn-edit {
            background: #4a4a4a;
            color: white;
        }

        /* Пустое состояние */
        .empty {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        /* Уведомление */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            animation: fadeOut 3s forwards;
            animation-delay: 2s;
        }

        @keyframes fadeOut {
            to { opacity: 0; visibility: hidden; }
        }

        .alert-success {
            background: rgba(46, 139, 87, 0.2);
            border: 1px solid #2e8b57;
            color: #90ee90;
        }

        /* Фильтры */
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filters input,
        .filters select {
            padding: 10px 15px;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 25px;
            color: var(--text);
        }

        /* Модальное окно */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.9);
            z-index: 1000;
            padding: 40px;
            overflow: auto;
        }

        .modal.active {
            display: block;
        }

        .modal-content {
            max-width: 800px;
            margin: 0 auto;
            background: var(--bg-light);
            border-radius: 15px;
            padding: 30px;
        }

        .modal-close {
            float: right;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 15px;
            }
            .data-table {
                font-size: 0.85rem;
            }
            .data-table th,
            .data-table td {
                padding: 10px;
            }
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>⚙️ Админ-панель | <?= e(SITE_NAME) ?\u003e</h1>
        <nav class="admin-nav">
            <a href="/admin/" class="active">Модерация</a>
            <a href="/admin/veterans.php">Все ветераны</a>
            <a href="/admin/stats.php">Статистика</a>
            <a href="/admin/logout.php">Выйти</a>
        </nav>
    </header>

    <div class="container">
        <?php if ($message): ?\u003e
        <div class="alert alert-success"><?= $message ?\u003e</div>
        <?php endif; ?\u003e

        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card pending">
                <h3>🕐 На модерации</h3>
                <div class="number"><?= $stats['pending'] ?\u003e</div>
            </div>
            <div class="stat-card">
                <h3>✅ Одобрено</h3>
                <div class="number"><?= $stats['total'] ?\u003e</div>
            </div>
            <div class="stat-card">
                <h3>📸 С фото</h3>
                <div class="number"><?= $stats['with_photo'] ?\u003e</div>
            </div>
        </div>

        <!-- Заявки на модерацию -->
        <section class="section">
            <h2>
                🕐 На модерации
                <?php if (count($pending) > 0): ?\u003e
                <span class="badge"><?= count($pending) ?\u003e</span>
                <?php endif; ?\u003e
            </h2>

            <?php if (empty($pending)): ?\u003e
            <div class="empty">
                <div class="empty-icon">✨</div>
                <p>Нет новых заявок. Всё проверено!</p>
            </div>
            <?php else: ?\u003e
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>Годы</th>
                        <th>Населённый пункт</th>
                        <th>Контакт</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $v): ?\u003e
                    <tr>
                        <td>
                            <div class="veteran-name">
                                <?= e($v['last_name'] . ' ' . $v['first_name'] . ' ' . $v['patronymic']) ?\u003e
                            </div>
                            <?php if ($v['rank']): ?\u003e
                            <div class="veteran-years"><?= e($v['rank']) ?\u003e</div>
                            <?php endif; ?\u003e
                        </td>
                        <td class="veteran-years">
                            <?= $v['birth_year'] ?\u003e — <?= $v['death_year'] ?? 'н.в.' ?\u003e
                        </td>
                        <td><?= e($v['settlement']) ?\u003e</td>
                        <td>
                            <?= e($v['submitted_by']) ?\u003e
                            <br>
                            <small><?= e($v['submitter_contact']) ?\u003e</small>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($v['created_at'])) ?\u003e</td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-view" onclick="viewVeteran(<?= $v['id'] ?\u003e)">
                                    👁️ Смотреть
                                </button>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $v['id'] ?\u003e">
                                    <button type="submit" name="action" value="approve" class="btn btn-approve">
                                        ✅ Одобрить
                                    </button>
                                </form>
                                <form method="post" style="display: inline;" 
                                      onsubmit="return confirm('Отклонить эту заявку?')">
                                    <input type="hidden" name="id" value="<?= $v['id'] ?\u003e">
                                    <button type="submit" name="action" value="reject" class="btn btn-reject">
                                        ❌ Отклонить
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?\u003e
                </tbody>
            </table>
            <?php endif; ?\u003e
        </section>

        <!-- Последние одобренные -->
        <section class="section">
            <h2>✅ Последние добавленные</h2>
            
            <?php if (empty($approved['items'])): ?\u003e
            <div class="empty">
                <p>Пока нет одобренных ветеранов</p>
            </div>
            <?php else: ?\u003e
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>Просмотров</th>
                        <th>Добавлен</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($approved['items'], 0, 10) as $v): ?\u003e
                    <tr>
                        <td>
                            <div class="veteran-name">
                                <?= e($v['last_name'] . ' ' . $v['first_name']) ?\u003e
                            </div>
                        </td>
                        <td><?= number_format($v['views_count']) ?\u003e</td>
                        <td><?= date('d.m.Y', strtotime($v['created_at'])) ?\u003e</td>
                        <td>
                            <a href="/veteran.php?id=<?= $v['id'] ?\u003e" class="btn btn-view" target="_blank">
                                Открыть
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?\u003e
                </tbody>
            </table>
            
            <p style="margin-top: 15px;">
                <a href="/admin/veterans.php" class="btn btn-view">Смотреть все →</a>
            </p>
            <?php endif; ?\u003e
        </section>
    </div>

    <!-- Модальное окно просмотра -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">×</span>
            <div id="modalBody">Загрузка...</div>
        </div>
    </div>

    <script>
        function viewVeteran(id) {
            document.getElementById('viewModal').classList.add('active');
            // Здесь можно загрузить данные через AJAX
            document.getElementById('modalBody').innerHTML = 
                '<iframe src="/veteran.php?id=' + id + '" style="width:100%;height:500px;border:none;"></iframe>';
        }

        function closeModal() {
            document.getElementById('viewModal').classList.remove('active');
        }

        // Закрытие по Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>
