<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Auth\WorkspaceContext;
use App\Core\CSRF;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Comment;
use App\Models\Document;

final class CommentController
{
    public function create(Request $req): Response
    {
        if (!WorkspaceContext::can('write')) {
            return (new Response())->html('You do not have permission to comment', 403);
        }

        $body = $this->body($req);
        if (!$this->validCsrf($req, $body)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $document = $this->scopedDocument($req);
        if ($document === null) {
            return (new Response())->html('Document not found', 404);
        }

        $commentBody = is_string($body['body'] ?? null) ? trim($body['body']) : '';
        if ($commentBody === '' || mb_strlen($commentBody) > 5000) {
            return $this->invalidInput($req, 'Comment must be between 1 and 5000 characters');
        }

        $parentId = null;
        if (($body['parent_id'] ?? '') !== '') {
            if (!is_scalar($body['parent_id']) || !ctype_digit((string) $body['parent_id'])) {
                return $this->invalidInput($req, 'Invalid parent comment');
            }
            $parent = Comment::findById((int) $body['parent_id']);
            if ($parent === null || (int) $parent['document_id'] !== (int) $document['id']) {
                return (new Response())->html('Comment not found', 404);
            }
            if ($parent['parent_id'] !== null) {
                return $this->invalidInput($req, 'Replies can only target top-level comments');
            }
            $parentId = (int) $parent['id'];
        }

        $comment = Comment::create((int) $document['id'], (int) Auth::id(), $parentId, $commentBody);
        if ($this->expectsJson($req)) {
            return (new Response())->json([
                'id' => $comment['id'],
                'body' => $comment['body'],
                'author_name' => $comment['author_name'],
                'created_at' => $comment['created_at'],
                'parent_id' => $comment['parent_id'],
            ], 201);
        }

        Session::flash('success', 'Comment added');

        return (new Response())->redirect('/w/' . $req->param('slug') . '/documents/' . $document['id'] . '#comments');
    }

    public function delete(Request $req): Response
    {
        if (!WorkspaceContext::can('write')) {
            return (new Response())->html('You do not have permission to comment', 403);
        }

        $body = $this->body($req);
        if (!$this->validCsrf($req, $body)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $document = $this->scopedDocument($req);
        if ($document === null) {
            return (new Response())->html('Document not found', 404);
        }

        $commentId = $req->param('comment_id');
        $comment = $commentId !== null && ctype_digit($commentId) ? Comment::findById((int) $commentId) : null;
        if ($comment === null || (int) $comment['document_id'] !== (int) $document['id']) {
            return (new Response())->html('Comment not found', 404);
        }

        $userId = (int) Auth::id();
        if ((int) $comment['author_id'] !== $userId && !WorkspaceContext::can('manage')) {
            return (new Response())->html('You do not have permission to delete this comment', 403);
        }

        if ((int) $comment['author_id'] === $userId) {
            Comment::delete((int) $comment['id'], $userId);
        } else {
            Comment::deleteByModerator((int) $comment['id']);
        }

        if ($this->expectsJson($req)) {
            return (new Response())->json(['deleted' => true]);
        }

        return (new Response())->redirect('/w/' . $req->param('slug') . '/documents/' . $document['id'] . '#comments');
    }

    /** @return array<string, mixed> */
    private function body(Request $req): array
    {
        return is_array($req->body) ? $req->body : [];
    }

    /** @param array<string, mixed> $body */
    private function validCsrf(Request $req, array $body): bool
    {
        $token = $body['_token'] ?? null;
        if (is_string($token) && CSRF::verify($token)) {
            return true;
        }

        foreach ($req->headers as $name => $value) {
            if (strtolower($name) === 'x-csrf-token' && CSRF::verify($value)) {
                return true;
            }
        }

        return false;
    }

    private function expectsJson(Request $req): bool
    {
        foreach ($req->headers as $name => $value) {
            if (strtolower($name) === 'accept' && str_contains(strtolower($value), 'application/json')) {
                return true;
            }
        }

        return false;
    }

    private function scopedDocument(Request $req): ?array
    {
        $id = $req->param('id');
        if ($id === null || !ctype_digit($id)) {
            return null;
        }

        $document = Document::findById((int) $id);
        if ($document === null || (int) $document['workspace_id'] !== (int) $req->workspace['id']) {
            return null;
        }

        return $document;
    }

    private function invalidInput(Request $req, string $message): Response
    {
        if ($this->expectsJson($req)) {
            return (new Response())->json(['error' => $message], 422);
        }

        Session::flash('error', $message);

        return (new Response())->redirect('/w/' . $req->param('slug') . '/documents/' . $req->param('id') . '#comments');
    }
}