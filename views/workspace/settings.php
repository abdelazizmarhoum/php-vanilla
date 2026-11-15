<?php
declare(strict_types=1);

use App\Auth\WorkspaceContext;
use App\Core\CSRF;

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Workspace settings</h1>
<form method="post" action="/w/<?= e($workspace['slug']) ?>/settings" class="mb-4">
    <?= CSRF::field() ?>
    <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input class="form-control" id="name" name="name" type="text" value="<?= e($workspace['name']) ?>" minlength="2" maxlength="80" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="slug">Slug</label>
        <input class="form-control" id="slug" name="slug" type="text" value="<?= e($workspace['slug']) ?>" required>
    </div>
    <button class="btn btn-primary" type="submit">Save changes</button>
</form>
<h2 class="h4">Members</h2>
<table class="table">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
    <tbody>
    <?php foreach ($members as $member): ?>
        <tr>
            <td><?= e($member['name']) ?></td>
            <td><?= e($member['email']) ?></td>
            <td><?= e($member['role']) ?></td>
            <td><?= e($member['created_at']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php if (WorkspaceContext::can('delete')): ?>
    <div class="border border-danger rounded p-3 mt-4">
        <h2 class="h5 text-danger">Danger zone</h2>
        <form method="post" action="/w/<?= e($workspace['slug']) ?>/delete">
            <?= CSRF::field() ?>
            <button class="btn btn-danger" type="submit">Delete workspace</button>
        </form>
    </div>
<?php endif; ?>