<?php
declare(strict_types=1);

use App\Core\CSRF;

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Create account</h1>
<form method="post" action="/register">
    <?= CSRF::field() ?>
    <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input class="form-control" id="name" name="name" type="text" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input class="form-control" id="email" name="email" type="email" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input class="form-control" id="password" name="password" type="password" minlength="8" required>
        <small class="form-text text-body-secondary">Password must be at least 8 characters.</small>
    </div>
    <button class="btn btn-primary" type="submit">Create account</button>
</form>
<p class="mt-3"><a href="/login">Sign in</a></p>
