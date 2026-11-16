<?php
declare(strict_types=1);

use App\Core\CSRF;

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1>Invite members to <?= e($workspace['name']) ?></h1>
<form method="post" action="/w/<?= e($workspace['slug']) ?>/invite" class="mb-4">
    <?= CSRF::field() ?>
    <div class="mb-3">
        <label class="form-label" for="invite-email">Email</label>
        <input class="form-control" id="invite-email" name="email" type="email" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="invite-role">Role</label>
        <select class="form-select" id="invite-role" name="role">
            <option value="admin">Admin</option>
            <option value="member" selected>Member</option>
            <option value="viewer">Viewer</option>
        </select>
    </div>
    <button class="btn btn-primary" type="submit">Send invitation</button>
</form>
<h2>Pending invitations</h2>
<?php if ($pending === []): ?>
    <p>No pending invitations</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Email</th><th>Role</th><th>Invited by</th><th>Expires</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($pending as $invitation): ?>
                <tr>
                    <td><?= e($invitation['email']) ?></td>
                    <td><?= e($invitation['role']) ?></td>
                    <td><?= e($invitation['inviter_name']) ?></td>
                    <td><?= e($invitation['expires_at']) ?></td>
                    <td>
                        <form method="post" action="/w/<?= e($workspace['slug']) ?>/invite/<?= e($invitation['id']) ?>/revoke">
                            <?= CSRF::field() ?>
                            <button class="btn btn-sm btn-outline-danger" type="submit">Revoke</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
