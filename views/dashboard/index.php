<?php
declare(strict_types=1);

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Dashboard</h1>
<div class="card mb-4">
    <div class="card-body">
        <p><strong>Name:</strong> <?= e($user['name']) ?></p>
        <p><strong>Email:</strong> <?= e($user['email']) ?></p>
        <p><strong>Member since:</strong> <?= e($user['created_at']) ?></p>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Your workspaces</h2>
    <a class="btn btn-primary" href="/workspaces/create">Create workspace</a>
</div>
<?php if ($workspaces === []): ?>
    <p>You don't belong to any workspaces yet</p>
<?php else: ?>
    <div class="list-group mb-3">
        <?php foreach ($workspaces as $workspace): ?>
            <a class="list-group-item list-group-item-action d-flex justify-content-between" href="/w/<?= e($workspace['slug']) ?>">
                <span><?= e($workspace['name']) ?></span>
                <span class="badge text-bg-secondary"><?= e($workspace['role']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<a href="/workspaces">View all</a>