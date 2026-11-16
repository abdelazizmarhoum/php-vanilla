<?php
declare(strict_types=1);

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Invalid verification link</h1>
<p><?= e($message) ?></p>
<a href="/login">Back to sign in</a>
