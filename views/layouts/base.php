<?php
declare(strict_types=1);

use App\Auth\Auth;
use App\Core\CSRF;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e($csrf) ?>">
    <title><?= e($title ?? 'App') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand" href="/">Team Workspace</a>
            <div class="navbar-nav">
                <a class="nav-link" href="/">Home</a>
                <a class="nav-link" href="/about">About</a>
                <?php if (Auth::check()): ?>
                    <?php $signedInUser = Auth::user(); ?>
                    <a class="nav-link" href="/dashboard">Signed in as <?= e($signedInUser['name']) ?></a>
                    <a class="nav-link" href="/workspaces">Workspaces</a>
                    <form class="d-inline" method="post" action="/logout">
                        <?= CSRF::field() ?>
                        <button class="nav-link btn btn-link" type="submit">Log out</button>
                    </form>
                <?php else: ?>
                    <a class="nav-link" href="/login">Sign in</a>
                    <a class="nav-link" href="/register">Create account</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container py-4"><?= $content ?></main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>