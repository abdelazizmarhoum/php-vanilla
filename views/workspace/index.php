<?php
declare(strict_types=1);

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Workspaces</h1>
<a class="btn btn-primary mb-4" href="/workspaces/create">Create workspace</a>
<?php if ($workspaces === []): ?>
    <p>You don't belong to any workspaces yet</p>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($workspaces as $workspace): ?>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5"><a href="/w/<?= e($workspace['slug']) ?>"><?= e($workspace['name']) ?></a></h2>
                        <span class="badge text-bg-secondary"><?= e($workspace['role']) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>