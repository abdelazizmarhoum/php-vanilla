<?php
declare(strict_types=1);

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>History for <?= e($document['title']) ?></h1>
<div class="list-group mb-3">
    <?php foreach ($versions as $version): ?>
        <a class="list-group-item list-group-item-action" href="/w/<?= e($workspace['slug']) ?>/documents/<?= e($document['id']) ?>/history/<?= e($version['id']) ?>">
            <strong><?= e($version['title']) ?></strong>
            <span class="d-block text-body-secondary">Edited by <?= e($version['editor_name']) ?> on <?= e($version['created_at']) ?></span>
        </a>
    <?php endforeach; ?>
</div>
<a href="/w/<?= e($workspace['slug']) ?>/documents/<?= e($document['id']) ?>">Back to document</a>