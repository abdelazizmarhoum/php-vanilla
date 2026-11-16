<?php
declare(strict_types=1);

use App\Core\CSRF;

require dirname(__DIR__) . '/partials/flash.php';
?>
<p>This invitation was for <?= e($invitation['email']) ?>, but you are logged in as <?= e($current_user_email) ?>.</p>
<form method="post" action="/logout">
    <?= CSRF::field() ?>
    <button class="btn btn-outline-secondary" type="submit">Log out</button>
</form>
<a href="/">Back to home</a>
