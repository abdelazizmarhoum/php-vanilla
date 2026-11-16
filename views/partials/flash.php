<?php
declare(strict_types=1);

use App\Core\Session;

$success = Session::getFlash('success');
$error = Session::getFlash('error');
$info = Session::getFlash('info');
$devVerifyUrl = Session::getFlash('dev_verify_url');
?>
<?php if ($success !== null): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error !== null): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($info !== null): ?>
    <div class="alert alert-info"><?= e($info) ?></div>
<?php endif; ?>
<?php if ($devVerifyUrl !== null): ?>
    <div class="alert alert-secondary"><?= e($devVerifyUrl) ?></div>
<?php endif; ?>
