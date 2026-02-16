<?php
// Конфигурация базы данных
// НА БЕГЕТЕ: создай базу в панели управления и впиши данные здесь

define('DB_HOST', 'localhost');
define('DB_NAME', 'jonni1988_museum');  // ← ТВОЯ БАЗА
define('DB_USER', 'jonni1988_museum');  // ← Обычно совпадает с именем базы на Бегете
define('DB_PASS', 'ТВОЙ_ПАРОЛЬ_ОТ_БАЗЫ');  // ← ПАРОЛЬ КОТОРЫЙ ЗАДАЛ ПРИ СОЗДАНИИ

// Настройки сайта
define('SITE_NAME', 'Память Олёкминского края');
define('SITE_URL', 'https://память.мояолекма.рф'); // ← ТВОЙ КИРИЛЛИЧЕСКИЙ ДОМЕН

// Telegram бот для уведомлений (опционально)
define('TG_BOT_TOKEN', '');
define('TG_ADMIN_CHAT_ID', '');

// Загрузка файлов
define('UPLOAD_DIR', __DIR__ . '/../../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Пагинация
define('ITEMS_PER_PAGE', 20);

// Подключение к БД
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Сессия
session_start();

// CSRF защита
function generateToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Проверка авторизации
function isAdmin() {
    return isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /login.php');
        exit;
    }
}

// Безопасный вывод
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
