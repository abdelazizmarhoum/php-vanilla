<?php
declare(strict_types=1);

use App\Core\CSRF;

require dirname(__DIR__) . '/partials/flash.php';
?>
<h1><?= e($document['title']) ?></h1>
<p class="text-body-secondary">By <?= e($document['author_name']) ?> · <?= e($document['updated_at']) ?></p>
<div id="document-view" class="card mb-3">
    <div class="card-body"><pre class="mb-0"><?= e($document['content']) ?></pre></div>
</div>
<?php if ($canWrite): ?>
    <div id="document-editor" class="card mb-3" hidden>
        <div class="card-body">
            <form method="post" action="/w/<?= e($workspace['slug']) ?>/documents/<?= e($document['id']) ?>/update">
                <?= CSRF::field() ?>
                <div class="mb-3">
                    <label class="form-label" for="edit-title">Title</label>
                    <input class="form-control" id="edit-title" name="title" type="text" value="<?= e($document['title']) ?>" maxlength="200" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="edit-content">Content</label>
                    <textarea class="form-control font-monospace" id="edit-content" name="content" rows="20"><?= e($document['content']) ?></textarea>
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