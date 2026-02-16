<?php
/**
 * Автодеплой с GitHub на Бегет
 * Webhook скрипт для обновления сайта при push в репозиторий
 */

@error_reporting(0);
@ini_set('display_errors', 0);

// 🔐 Секретный токен (сгенерируй свой случайный!)
$secret = 'a1b2c3d4e5f678901234567890123456'; // ЗАМЕНИ НА СВОЙ!

// 📁 Директория с файлами сайта (public_html)
define('REPO', $_SERVER['DOCUMENT_ROOT']);

// 📝 Лог-файл (рядом с public_html)
define('LOGFILE', dirname(REPO) . '/deploy.log');

// 🔧 SSH параметры (без проверки хоста для GitHub)
$ssh_params_array = [
    'StrictHostKeyChecking' => 'no',
    'UserKnownHostsFile' => '/dev/null',
    'LogLevel' => 'quiet',
    'HashKnownHosts' => 'no',
];

$ssh_params = '';
foreach ($ssh_params_array as $key => $value) {
    $ssh_params .= "-o $key=$value ";
}
putenv("GIT_SSH_COMMAND=ssh $ssh_params");

// Проверка секретного токена
if (!isset($_REQUEST[$secret])) {
    http_response_code(403);
    exit('Access denied');
}

// Логирование
function dlog($text) {
    $line = date('Y-m-d H:i:s') . " " . $text . "\n";
    error_log($line, 3, LOGFILE);
}

// Переходим в директорию сайта
chdir(REPO);
dlog("=== Deploy started from " . $_SERVER['REMOTE_ADDR'] . " ===");

$error = false;
$output_lines = [];

// 🔄 Команды для обновления
$cmds = [
    'git fetch origin main',
    'git reset --hard origin/main',
    'git clean -fd',
];

foreach ($cmds as $cmd) {
    dlog("Executing: $cmd");
    exec("$cmd 2>&1", $out, $code);
    
    $output = implode("\n", $out);
    dlog("Output: $output");
    dlog("Exit code: $code");
    
    if ($code != 0) {
        dlog("ERROR: Command failed with code $code");
        $error = true;
        break;
    }
}

if ($error) {
    http_response_code(500);
    echo "FAILED\n";
    echo "Check deploy.log for details\n";
    dlog("=== Deploy FAILED ===");
} else {
    echo "OK\n";
    echo "Deploy successful at " . date('Y-m-d H:i:s') . "\n";
    dlog("=== Deploy SUCCESS ===");
}
