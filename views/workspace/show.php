<?php
declare(strict_types=1);

use App\Auth\WorkspaceContext;

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1><?= e($workspace['name']) ?></h1>
<p><span class="badge text-bg-secondary"><?= e($role) ?></span></p>
<p>Member since: <?= e($membership['created_at'] ?? '') ?></p>
<p>Members: <?= e($memberCount) ?></p>
<?php if (WorkspaceContext::can('manage')): ?>
    <a class="btn btn-outline-primary me-2" href="/w/<?= e($workspace['slug']) ?>/settings">Settings</a>
    <a class="btn btn-outline-secondary" href="/w/<?= e($workspace['slug']) ?>/invite">Invite</a>
<?php endif; ?>
<p class="mt-4">Documents will appear here soon.</p>