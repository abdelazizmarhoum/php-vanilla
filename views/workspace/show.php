<?php
declare(strict_types=1);

use App\Auth\WorkspaceContext;

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1><?= e($workspace['name']) ?></h1>
<p><span class="badge text-bg-secondary"><?= e($role) ?></span></p>
<p>Member since: <?= e($membership['created_at'] ?? '') ?></p>
<p>Members: <?= e($memberCount) ?></p>
<?php if (WorkspaceContext::can('manage')): ?>
    <a class="btn btn-outline-primary me-2" href="/w/<?= e($workspace['slug']) ?>/settings">Settings</a>
    <a class="btn btn-outline-secondary" href="/w/<?= e($workspace['slug']) ?>/invite">Invite</a>
<?php endif; ?>
<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h2 class="h4 mb-0">Recent documents</h2>
    <div>
        <a class="btn btn-outline-secondary me-2" href="/w/<?= e($workspace['slug']) ?>/documents">View all documents</a>
        <?php if (WorkspaceContext::can('write')): ?><a class="btn btn-primary" href="/w/<?= e($workspace['slug']) ?>/documents/create">New document</a><?php endif; ?>
    </div>
</div>
<?php if ($recentDocuments === []): ?>
    <p>No documents yet</p>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($recentDocuments as $document): ?>
            <a class="list-group-item list-group-item-action" href="/w/<?= e($workspace['slug']) ?>/documents/<?= e($document['id']) ?>">
                <strong><?= e($document['title']) ?></strong>
                <span class="d-block text-body-secondary"><?= e($document['updated_at']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<h2 class="h4 mt-4">Recent activity</h2>
<div id="activity-feed"
     data-workspace-slug="<?= e($workspace['slug']) ?>"
     data-last-id="<?= e($activity[0]['id'] ?? 0) ?>">
    <?php if ($activity === []): ?>
        <p class="activity-empty">No activity yet.</p>
    <?php else: ?>
        <?php foreach ($activity as $item): ?>
            <?php require dirname(__DIR__) . '/partials/activity-item.php'; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<a href="/w/<?= e($workspace['slug']) ?>/activity">View all activity</a>
<script src="/assets/js/activity.js" defer></script>