<?php
declare(strict_types=1);

use App\Core\CSRF;

require dirname(__DIR__) . '/partials/flash.php';
?>
<p>Version from <?= e($version['created_at']) ?> by <?= e($version['editor_name']) ?></p>
<h2><?= e($version['title']) ?></h2>
<pre><?= e($version['content']) ?></pre>
<?php if ($canWrite): ?>
    <form method="post" action="/w/<?= e($workspace['slug']) ?>/documents/<?= e($document['id']) ?>/history/<?= e($version['id']) ?>/restore" class="mb-3">
        <?= CSRF::field() ?>
        <button class="btn btn-primary" type="submit">Restore this version</button>
    </form>
<?php endif; ?>
<a href="/w/<?= e($workspace['slug']) ?>/documents/<?= e($document['id']) ?>/history">Back to history</a>