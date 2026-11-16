<?php
declare(strict_types=1);

use App\Core\CSRF;

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Sign in</h1>
<form method="post" action="/login">
    <?= CSRF::field() ?>
    <input type="hidden" name="invitation" value="<?= e($invitation_token ?? '') ?>">
    <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input class="form-control" id="email" name="email" type="email" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input class="form-control" id="password" name="password" type="password" required>
    </div>
    <button class="btn btn-primary" type="submit">Sign in</button>
</form>
<p class="mt-3"><a href="/register">Create account</a></p>
