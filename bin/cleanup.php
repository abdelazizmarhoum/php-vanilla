<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/src/bootstrap.php';

$deleted = [
    'remember_tokens' => \App\Core\Database::q(
        "DELETE FROM remember_tokens WHERE expires_at < NOW()",
    )->rowCount(),
    'password_resets' => \App\Core\Database::q(
        "DELETE FROM password_resets WHERE expires_at < NOW() - INTERVAL '7 days'",
    )->rowCount(),
    'email_verifications' => \App\Core\Database::q(
        "DELETE FROM email_verifications WHERE expires_at < NOW() - INTERVAL '7 days'",
    )->rowCount(),
    'login_attempts' => \App\Core\Database::q(
        "DELETE FROM login_attempts WHERE created_at < NOW() - INTERVAL '30 days'",
    )->rowCount(),
];

foreach ($deleted as $table => $count) {
    echo $table . ': ' . $count . PHP_EOL;
}
