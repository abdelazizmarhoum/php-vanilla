<?php
declare(strict_types=1);

use App\Core\CSRF;

require dirname(__DIR__) . '/partials/flash.php';

$topComments = [];
$repliesByParent = [];
foreach ($comments as $comment) {
    if ($comment['parent_id'] === null) {
        $topComments[] = $comment;
    } else {
        $repliesByParent[(string) $comment['parent_id']][] = $comment;
    }
}
?>
<h1><?= e($document['title']) ?></h1>
<p class="text-body-secondary">By <?= e($document['author_name']) ?> · <?= e($document['updated_at']) ?></p>
<div id="document-view" class="card mb-3">
    <div class="card-body"><div class="markdown-body"><?= \App\Core\Markdown::toHtml($document['content']) ?></div></div>
</div>
<section id="comments" class="mt-4">
    <h2 class="h4">Comments (<?= e($comment_count) ?>)</h2>
    <?php if ($canWrite): ?>
        <form method="post" action="/w/<?= e($workspace['slug']) ?>/documents/<?= e($document['id']) ?>/comments" class="mb-4">
            <?= CSRF::field() ?>
            <textarea class="form-control mb-2" name="body" rows="3" maxlength="5000" required></textarea>
            <button class="btn btn-primary" type="submit">Post comment</button>
        </form>
    <?php endif; ?>
    <?php foreach ($topComments as $comment): ?>
        <?php
        $replies = $repliesByParent[(string) $comment['id']] ?? [];
        $isReply = false;
        require dirname(__DIR__) . '/partials/comment.php';
        ?>
    <?php endforeach; ?>
</section>
<?php if ($canWrite): ?>
    <div id="document-editor" class="card mb-3" hidden>
        <div class="card-body">
            <form method="post" action="/w/<?= e($workspace['slug']) ?>/documents/<?= e($document['id']) ?>/update">
                <?= CSRF::field() ?>
                <div class="mb-3">
                    <label class="form-label" for="edit-title">Title</label>
                    <input class="form-control" id="edit-title" name="title" type="text" value="<?= e($document['title']) ?>" maxlength="200" required>
                </div>
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label" for="document-content">Content</label>
                        <textarea class="form-control font-monospace" id="document-content" name="content" rows="20"><?= e($document['content']) ?></textarea>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">Preview</label>
                        <div id="markdown-preview" class="markdown-body border rounded p-3"></div>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">Save</button>
            </form>
        </div>
    </div>
<?php endif; ?>
<div class="d-flex gap-2">
    <?php if ($canWrite): ?><button class="btn btn-outline-primary" id="edit-document" type="button">Edit</button><?php endif; ?>
    <a class="btn btn-outline-secondary" href="/w/<?= e($workspace['slug']) ?>/documents/<?= e($document['id']) ?>/history">History</a>
    <?php if ($canWrite): ?>
        <form id="delete-document" method="post" action="/w/<?= e($workspace['slug']) ?>/documents/<?= e($document['id']) ?>/delete">
            <?= CSRF::field() ?>
            <button class="btn btn-outline-danger" type="submit">Delete</button>
        </form>
    <?php endif; ?>
</div>
<?php if ($canWrite): ?>
<script>
const editor = document.getElementById('document-editor');
const view = document.getElementById('document-view');
document.getElementById('edit-document').addEventListener('click', () => {
    const showingEditor = !editor.hidden;
    editor.hidden = showingEditor;
    view.hidden = !showingEditor;
});
document.getElementById('delete-document').addEventListener('submit', (event) => {
    if (!window.confirm('Delete this document?')) event.preventDefault();
});
</script>
<?php endif; ?>
<script src="/assets/js/comments.js" defer></script>
<?php if ($canWrite): ?><script src="/assets/js/editor.js" defer></script><?php endif; ?>