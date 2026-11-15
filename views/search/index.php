<?php
declare(strict_types=1);

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Search</h1>
<form method="get" action="/search" class="mb-4">
    <input class="form-control" type="search" name="q" value="<?= e($q) ?>" autofocus>
    <button class="btn btn-primary mt-2" type="submit">Search</button>
</form>
<?php if ($q !== ''): ?>
    <p><?= e(count($results)) ?> result(s) for "<?= e($q) ?>"</p>
    <?php if ($results === []): ?>
        <p>No matches</p>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($results as $result): ?>
                <a class="list-group-item list-group-item-action" href="/w/<?= e($result['workspace_slug']) ?>/documents/<?= e($result['id']) ?>">
                    <strong><?= e($result['title']) ?></strong>
                    <small class="d-block text-body-secondary"><?= e($result['workspace_name']) ?> · <?= e($result['updated_at']) ?></small>
                    <div><?= safe_highlight((string) $result['snippet']) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <p>Search across all your workspaces</p>
<?php endif; ?>
