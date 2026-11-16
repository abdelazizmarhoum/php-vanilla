<?php
declare(strict_types=1);

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>You've been invited to <?= e($workspace['name']) ?></h1>
<p>Sign in or create an account to accept.</p>
<a href="/login?invitation=<?= e($token) ?>">Sign in</a>
<a href="/register?invitation=<?= e($token) ?>">Create an account</a>
