<?php
declare(strict_types=1);

use App\Core\CSRF;

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>New document</h1>
<form method="post" action="/w/<?= e($workspace['slug']) ?>/documents">
    <?= CSRF::field() ?>
    <div class="mb-3">
        <label class="form-label" for="title">Title</label>
        <input class="form-control" id="title" name="title" type="text" maxlength="200" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="content">Content</label>
        <textarea class="form-control font-monospace" id="content" name="content" rows="20"></textarea>
    </div>
    <button class="btn btn-primary" type="submit">Create document</button>
</form>