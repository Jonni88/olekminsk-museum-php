<?php
require_once __DIR__ . '/../../src/config/database.php';

$error = '';

// Если уже авторизован — редирект в админку
if (isAdmin()) {
    header('Location: /admin/');
    exit;
}

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: /admin/');
            exit;
        } else {
            $error = 'Неверный логин или пароль';
        }
    } else {
        $error = 'Введите логин и пароль';
    }
}
?\>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | Админ-панель</title>
    <link href="https://fonts.googleapis.com/css2?family=PT+Serif:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #0d0d0d 0%, #1a1a2e 50%, #0f0f23 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            background: rgba(26, 26, 46, 0.9);
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(212, 175, 55, 0.2);
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }

        .login-box h1 {
            font-family: 'PT Serif', serif;
            color: #d4af37;
            text-align: center;
            margin-bottom: 10px;
        }

        .login-box .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #e0e0e0;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            background: rgba(0,0,0,0.3);
            border: 2px solid rgba(212, 175, 55, 0.2);
            border-radius: 10px;
            color: #e0e0e0;
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #d4af37;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%);
            color: #1a1a1a;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .error {
            background: rgba(139, 0, 0, 0.2);
            border: 1px solid #8b0000;
            color: #ff6b6b;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #888;
            text-decoration: none;
        }

        .back-link:hover {
            color: #d4af37;
        }

        .flame {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 20px;
            animation: flicker 2s ease-in-out infinite alternate;
        }

        @keyframes flicker {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="flame">🔥</div>
        <h1>Память Олёкминского края</h1>
        <p class="subtitle">Вход в админ-панель</p>
        
        <?php if ($error): ?\u003e
        <div class="error"><?= $error ?\u003e</div>
        <?php endif; ?\u003e
        
        <form method="post">
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">Войти</button>
        </form>
        
        <a href="/" class="back-link">← Вернуться на сайт</a>
    </div>
</body>
</html>
