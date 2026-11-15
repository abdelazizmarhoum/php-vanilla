<?php
declare(strict_types=1);

use App\Core\CSRF;

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Create workspace</h1>
<form method="post" action="/workspaces">
    <?= CSRF::field() ?>
    <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input class="form-control" id="name" name="name" type="text" minlength="2" maxlength="80" required>
    </div>
    <button class="btn btn-primary" type="submit">Create workspace</button>
</form>