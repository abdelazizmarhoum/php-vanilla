<?php
declare(strict_types=1);

use App\Core\CSRF;

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Set a new password</h1>
<form method="post" action="/reset-password/<?= e($token) ?>">
    <?= CSRF::field() ?>
    <div class="mb-3">
        <label class="form-label" for="new-password">New password</label>
        <input class="form-control" id="new-password" name="password" type="password" minlength="8" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password-confirmation">Confirm password</label>
        <input class="form-control" id="password-confirmation" name="password_confirmation" type="password" minlength="8" required>
    </div>
    <button class="btn btn-primary" type="submit">Update password</button>
</form>
