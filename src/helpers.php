<?php
declare(strict_types=1);

use App\Core\App;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function config(string $key, mixed $default = null): mixed
{
    return App::config($key, $default);
}

function url(string $path = ''): string
{
    $base = (string) config('app.url', '');
    if ($path === '') return $base;
    return $base . '/' . ltrim($path, '/');
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';

    return trim($text, '-');
}

function activity(
    int $workspaceId,
    string $action,
    ?string $subjectType = null,
    ?int $subjectId = null,
    array $meta = [],
): void {
    try {
        \App\Models\ActivityLog::log(
            $workspaceId,
            \App\Auth\Auth::id(),
            $action,
            $subjectType,
            $subjectId,
            $meta,
        );
    } catch (\Throwable $exception) {
        file_put_contents('php://stderr', 'Activity logging failed: ' . $exception->getMessage() . PHP_EOL);
    }
}

function humanize_action(string $action, array $meta = []): string
{
    $title = isset($meta['title']) ? (string) $meta['title'] : '';
    $documentTitle = isset($meta['document_title']) ? (string) $meta['document_title'] : $title;

    return match ($action) {
        'document.created' => $title === '' ? 'created a document' : "created '{$title}'",
        'document.updated' => $title === '' ? 'updated a document' : "updated '{$title}'",
        'document.deleted' => $title === '' ? 'deleted a document' : "deleted '{$title}'",
        'document.restored' => $title === '' ? 'restored a document' : "restored '{$title}'",
        'comment.added' => $documentTitle === '' ? 'commented' : "commented on '{$documentTitle}'",
        'comment.deleted' => $documentTitle === '' ? 'deleted a comment' : "deleted a comment on '{$documentTitle}'",
        'workspace.created' => isset($meta['name']) ? "created workspace '{$meta['name']}'" : 'created a workspace',
        'workspace.updated' => isset($meta['name']) ? "renamed workspace to '{$meta['name']}'" : 'updated the workspace',
        default => $action,
    };
}