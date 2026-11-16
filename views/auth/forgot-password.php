<?php
declare(strict_types=1);

use App\Core\CSRF;

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Forgot password</h1>
<form method="post" action="/forgot-password">
    <?= CSRF::field() ?>
    <div class="mb-3">
        <label class="form-label" for="reset-email">Email</label>
        <input class="form-control" id="reset-email" name="email" type="email" required>
    </div>
    <button class="btn btn-primary" type="submit">Send reset link</button>
</form>
<p class="mt-3"><a href="/login">Back to sign in</a></p>
