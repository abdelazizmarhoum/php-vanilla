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
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="https://cdn.jsdelivr.net/npm/marked@12.0.0/marked.min.js" defer></script>
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
                    <div class="position-relative ms-2">
                        <form method="get" action="/search" class="d-flex">
                            <input id="nav-search" class="form-control form-control-sm" type="search" name="q" placeholder="Search" value="<?= e($q ?? '') ?>" autocomplete="off" data-api="/search/api">
                        </form>
                        <div id="nav-search-results" class="dropdown-menu"></div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" type="button">
                            <?= e($signedInUser['name']) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/dashboard">Dashboard</a></li>
                            <li><a class="dropdown-item" href="/workspaces">Workspaces</a></li>
                            <li><a class="dropdown-item" href="/search">Search</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="/logout" class="m-0">
                                    <?= CSRF::field() ?>
                                    <button class="dropdown-item" type="submit">Sign out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="nav-link" href="/login">Sign in</a>
                    <a class="nav-link" href="/register">Create account</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container py-4">
        <?php require __DIR__ . '/../partials/flash.php'; ?>
        <?php if (Auth::check() && Auth::user()['email_verified_at'] === null): ?>
            <div class="alert alert-warning">
                Your email is not verified.
                <form method="post" action="/verify-email/resend" class="d-inline">
                    <?= CSRF::field() ?>
                    <button class="btn btn-sm btn-warning" type="submit">Resend verification</button>
                </form>
            </div>
        <?php endif; ?>
        <?= $content ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (Auth::check()): ?><script src="/assets/js/search.js" defer></script><?php endif; ?>
</body>
</html>