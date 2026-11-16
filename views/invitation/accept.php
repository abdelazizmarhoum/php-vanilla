<?php
declare(strict_types=1);

use App\Core\CSRF;

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Join <?= e($workspace['name']) ?></h1>
<p>You've been invited as <?= e($invitation['role']) ?>.</p>
<form method="post" action="/invitations/<?= e($token) ?>">
    <?= CSRF::field() ?>
    <button class="btn btn-primary" type="submit">Accept invitation</button>
</form>
