<?php
declare(strict_types=1);

use Dotenv\Dotenv;

Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

return [
    'app' => [
        'env'   => $_ENV['APP_ENV']   ?? 'local',
        'debug' => ($_ENV['APP_ENV'] ?? 'local') === 'local'
            && filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOL),
        'url'   => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/'),
    ],
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => (int) ($_ENV['DB_PORT'] ?? 5432),
        'name' => $_ENV['DB_NAME'] ?? '',
        'user' => $_ENV['DB_USER'] ?? '',
        'pass' => $_ENV['DB_PASS'] ?? '',
    ],
    'session' => [
        'name'     => 'app_session',
        'lifetime' => 60 * 60 * 24,
        'secure'   => ($_ENV['APP_ENV'] ?? 'local') !== 'local',
        'httponly' => true,
        'samesite' => 'Lax',
    ],
    'mail' => [
        'log_path' => __DIR__ . '/../storage/logs/mail.log',
    ],
];