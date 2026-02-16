<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/models/VeteranModel.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация
    if (empty($_POST['last_name'])) {
        $errors[] = 'Укажите фамилию';
    }
    if (empty($_POST['first_name'])) {
        $errors[] = 'Укажите имя';
    }
    
    if (empty($errors)) {
        $veteranModel = new VeteranModel();
        
        // Обработка фото
        $photos = [];
        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                    $filename = uploadPhoto($tmpName, $_FILES['photos']['name'][$key]);
                    if ($filename) {
                        $photos[] = $filename;
                    }
                }
            }
        }
        
        // Сохраняем в БД
        $data = [
            'last_name' => trim($_POST['last_name']),
            'first_name' => trim($_POST['first_name']),
            'patronymic' => trim($_POST['patronymic'] ?? ''),
            'birth_year' => $_POST['birth_year'] ? intval($_POST['birth_year']) : null,
            'death_year' => $_POST['death_year'] ? intval($_POST['death_year']) : null,
            'settlement' => trim($_POST['settlement'] ?? ''),
            'rank' => trim($_POST['rank'] ?? ''),
            'awards' => trim($_POST['awards'] ?? ''),
            'biography' => trim($_POST['biography'] ?? ''),
            'front_path' => trim($_POST['front_path'] ?? ''),
            'submitted_by' => trim($_POST['submitted_by'] ?? 'Аноним'),
            'submitter_contact' => trim($_POST['submitter_contact'] ?? ''),
            'photos' => $photos
        ];
        
        $id = $veteranModel->create($data);
        
        if ($id) {
            $success = true;
            // Отправить уведомление админу (опционально)
            // notifyAdmin($id, $data);
        }
    }
}

function uploadPhoto($tmpName, $originalName) {
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($ext, $allowed)) {
        return false;
    }
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $uploadPath = UPLOAD_DIR . 'photos/' . $filename;
    
    if (move_uploaded_file($tmpName, $uploadPath)) {
        // Создаём миниатюру
        createThumbnail($uploadPath, UPLOAD_DIR . 'thumbs/' . $filename, 300, 300);
        return $filename;
    }
    
    return false;
}

function createThumbnail($src, $dst, $width, $height) {
    // Упрощённая версия - в продакшене используй GD или ImageMagick
    copy($src, $dst);
}
?\>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить героя | <?= e(SITE_NAME) ?\u003e</title>
    <link href="https://fonts.googleapis.com/css2?family=PT+Serif:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="main-nav">
        <div class="nav-container">
            <a href="/" class="nav-logo">ПАМЯТЬ</a>
            <ul class="nav-menu">
                <li><a href="/">Главная</a></li>
                <li><a href="/bessmertny-pol.php">Бессмертный полк</a></li>
                <li><a href="/add.php">Добавить героя</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <div class="add-form-section">
            <h1>➕ Добавить информацию о ветеране</h1>
            
            <?php if ($success): ?\u003e
            <div class="alert alert-success">
                <h3>Спасибо! ✅</h3>
                <p>Информация отправлена на модерацию. После проверки она появится на сайте.</p>
                <a href="/" class="btn btn-primary">Вернуться на главную</a>
            </div>
            <?php else: ?\u003e
            
            <?php if (!empty($errors)): ?\u003e
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?\u003e
                    <li><?= e($error) ?\u003e</li>
                    <?php endforeach; ?\u003e
                </ul>
            </div>
            <?php endif; ?\u003e

            <form method="post" enctype="multipart/form-data" class="add-form">
                <input type="hidden" name="csrf_token" value="<?= generateToken() ?\u003e">
                
                <section class="form-section">
                    <h3>👤 Основная информация</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Фамилия *</label>
                            <input type="text" name="last_name" required 
                                   value="<?= e($_POST['last_name'] ?? '') ?\u003e"
                                   placeholder="Иванов">
                        </div>
                        
                        <div class="form-group">
                            <label>Имя *</label>
                            <input type="text" name="first_name" required
                                   value="<?= e($_POST['first_name'] ?? '') ?\u003e"
                                   placeholder="Иван">
                        </div>
                        
                        <div class="form-group">
                            <label>Отчество</label>
                            <input type="text" name="patronymic"
                                   value="<?= e($_POST['patronymic'] ?? '') ?\u003e"
                                   placeholder="Петрович">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Год рождения</label>
                            <input type="number" name="birth_year" min="1900" max="2000"
                                   value="<?= e($_POST['birth_year'] ?? '') ?\u003e"
                                   placeholder="1920">
                        </div>
                        
                        <div class="form-group">
                            <label>Год смерти</label>
                            <input type="number" name="death_year" min="1941" max="2030"
                                   value="<?= e($_POST['death_year'] ?? '') ?\u003e"
                                   placeholder="1985">
                        </div>
                        
                        <div class="form-group">
                            <label>Населённый пункт</label>
                            <input type="text" name="settlement"
                                   value="<?= e($_POST['settlement'] ?? '') ?\u003e"
                                   placeholder="с. Олёкминск">
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3>🎖️ Воинская служба</h3>
                    
                    <div class="form-group">
                        <label>Воинское звание</label>
                        <input type="text" name="rank"
                               value="<?= e($_POST['rank'] ?? '') ?\u003e"
                               placeholder="красноармеец, сержант, лейтенант...">
                    </div>
                    
                    <div class="form-group">
                        <label>Награды</label>
                        <textarea name="awards" rows="2" placeholder="Орден Красной Звезды, медаль «За отвагу»..."><?= e($_POST['awards'] ?? '') ?\u003e</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Фронтовой путь (где воевал)</label>
                        <textarea name="front_path" rows="2" placeholder="Сталинград, Курская дуга, Берлин..."><?= e($_POST['front_path'] ?? '') ?\u003e</textarea>
                    </div>
                </section>

                <section class="form-section">
                    <h3>📝 Биография</h3>
                    
                    <div class="form-group">
                        <label>Биография, подвиги, воспоминания</label>
                        <textarea name="biography" rows="6" 
                                  placeholder="Расскажите о жизни ветерана, его подвигах, где служил, чем отличился..."><?= e($_POST['biography'] ?? '') ?\u003e</textarea>
                    </div>
                </section>

                <section class="form-section">
                    <h3>📸 Фотографии</label>
                    
                    <div class="form-group">
                        <input type="file" name="photos[]" multiple accept="image/*">
                        <p class="help-text">Можно выбрать несколько файлов. Максимальный размер: 5 МБ каждый.</p>
                    </div>
                </section>

                <section class="form-section">
                    <h3>📞 Ваши контакты</h3>
                    
                    <p class="help-text">Укажите, чтобы мы могли связаться при необходимости уточнить информацию.</p>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Ваше имя</label>
                            <input type="text" name="submitted_by"
                                   value="<?= e($_POST['submitted_by'] ?? '') ?\u003e"
                                   placeholder="Иван Иванов">
                        </div>
                        
                        <div class="form-group">
                            <label>Телефон или email</label>
                            <input type="text" name="submitter_contact"
                                   value="<?= e($_POST['submitter_contact'] ?? '') ?\u003e"
                                   placeholder="+7 (914) 123-45-67">
                        </div>
                    </div>
                </section>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">Отправить на модерацию</button>
                    <a href="/" class="btn btn-outline">Отмена</a>
                </div>
            </form>
            
            <?php endif; ?\u003e
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>© 2026 <?= e(SITE_NAME) ?\u003e</p>
        </div>
    </footer>
</body>
</html>
