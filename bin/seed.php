<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/src/bootstrap.php';

$email = 'demo@example.com';
if (\App\Models\User::findByEmail($email) !== null) {
    echo "Already seeded" . PHP_EOL;
    exit(0);
}

$algorithm = in_array('argon2id', password_algos(), true)
    ? PASSWORD_ARGON2ID
    : PASSWORD_DEFAULT;
$passwordHash = password_hash('demo1234', $algorithm);
if ($passwordHash === false) {
    throw new RuntimeException('Unable to hash demo password.');
}

$user = \App\Models\User::create('Demo User', $email, $passwordHash);
\App\Models\User::markEmailVerified((int) $user['id']);
$workspace = \App\Models\Workspace::create((int) $user['id'], 'Demo Workspace', 'demo-workspace');
\App\Models\Membership::add((int) $workspace['id'], (int) $user['id'], 'owner');
$welcome = \App\Models\Document::create(
    (int) $workspace['id'],
    (int) $user['id'],
    'Welcome',
    "# Welcome\n\nThis is your demo workspace.",
);
\App\Models\Document::create(
    (int) $workspace['id'],
    (int) $user['id'],
    'Markdown Test',
    "## Markdown\n\n**Bold**, *italic*, `code`, and a [link](https://example.com).",
);
\App\Models\Comment::create(
    (int) $welcome['id'],
    (int) $user['id'],
    null,
    'Welcome to the demo workspace.',
);

echo "Seeded demo workspace" . PHP_EOL;
