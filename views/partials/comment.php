<?php
declare(strict_types=1);

use App\Core\CSRF;

$isReply = $isReply ?? false;
$replies = $replies ?? [];
$canWrite = $canWrite ?? false;
$canManage = $canManage ?? false;
$currentUserId = $currentUserId ?? null;
?>
<div class="card comment-card mb-2 <?= $isReply ? 'ms-4' : '' ?>">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between">
            <strong><?= e($comment['author_name']) ?></strong>
            <small class="text-body-secondary"><?= e($comment['created_at']) ?></small>
        </div>
        <p class="mb-2"><?= nl2br(e($comment['body'])) ?></p>
        <?php if ($canWrite && !$isReply): ?>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-reply-toggle>Reply</button>
            <form class="reply-form mt-2" method="post" action="/w/<?= e($workspace['slug']) ?>/documents/<?= e($document['id']) ?>/comments" hidden>
                <?= CSRF::field() ?>
                <input type="hidden" name="parent_id" value="<?= e($comment['id']) ?>">
                <textarea class="form-control mb-2" name="body" rows="2" maxlength="5000" required></textarea>
                <button class="btn btn-sm btn-primary" type="submit">Post reply</button>
            </form>
        <?php endif; ?>
        <?php if ($canWrite && ((int) $comment['author_id'] === (int) $currentUserId || $canManage)): ?>
            <form class="d-inline" method="post" action="/w/<?= e($workspace['slug']) ?>/documents/<?= e($document['id']) ?>/comments/<?= e($comment['id']) ?>/delete">
                <?= CSRF::field() ?>
                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php if (!$isReply): ?>
    <?php foreach ($replies as $reply): ?>
        <?php $comment = $reply; $isReply = true; $replies = []; require __DIR__ . '/comment.php'; ?>
    <?php endforeach; ?>
<?php endif; ?>
