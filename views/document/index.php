<?php
declare(strict_types=1);

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Documents</h1>
<?php if ($canWrite): ?>
    <a class="btn btn-primary mb-4" href="/w/<?= e($workspace['slug']) ?>/documents/create">New document</a>
<?php endif; ?>
<?php if ($documents === []): ?>
    <p>No documents yet</p>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($documents as $document): ?>
            <a class="list-group-item list-group-item-action" href="/w/<?= e($workspace['slug']) ?>/documents/<?= e($document['id']) ?>">
                <div class="d-flex justify-content-between">
                    <strong><?= e($document['title']) ?></strong>
                    <small><?= e($document['updated_at']) ?></small>
                </div>
                <div class="text-body-secondary"><?= e($document['excerpt']) ?></div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>