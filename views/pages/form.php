<?php
declare(strict_types=1);

use App\Core\CSRF;
use App\Core\Session;

$lastMessage = Session::get('_form_last');
require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>CSRF Demo</h1>
<form method="post" action="/form">
    <?= CSRF::field() ?>
    <div class="mb-3">
        <label class="form-label" for="message">Message</label>
        <input class="form-control" id="message" name="message" type="text" value="<?= e($lastMessage ?? '') ?>">
    </div>
    <button class="btn btn-primary" type="submit">Submit</button>
</form>
