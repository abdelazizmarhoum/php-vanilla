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
<p>Your workspaces will appear here soon.</p>
