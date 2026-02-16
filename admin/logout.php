<?php
require_once __DIR__ . '/../../src/config/database.php';

// Очищаем сессию
$_SESSION = [];

// Удаляем cookie сессии
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

session_destroy();

header('Location: /admin/login.php');
exit;
