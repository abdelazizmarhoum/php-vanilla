<?php
declare(strict_types=1);

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Activity</h1>
<div class="card">
    <div class="card-body">
        <?php if ($activity === []): ?>
            <p class="mb-0">No activity yet.</p>
        <?php else: ?>
            <?php foreach ($activity as $item): ?>
                <?php require dirname(__DIR__) . '/partials/activity-item.php'; ?>
            <?php endforeach; ?>
            <p class="text-body-secondary mt-3 mb-0">More coming.</p>
        <?php endif; ?>
    </div>
</div>
