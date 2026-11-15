<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Auth\WorkspaceContext;
use App\Core\CSRF;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Comment;
use App\Models\Document;
use App\Models\DocumentVersion;

final class DocumentController
{
    public function index(Request $req): Response
    {
        $workspace = $req->workspace;
        $documents = Document::listForWorkspace((int) $workspace['id']);

        return View::render('document/index', [
            'title' => 'Documents',
            'workspace' => $workspace,
            'documents' => $documents,
            'canWrite' => WorkspaceContext::can('write'),
        ]);
    }

    public function show(Request $req): Response
    {
        $document = $this->scopedDocument($req);
        if ($document === null) {
            return (new Response())->html('Document not found', 404);
        }

        return View::render('document/show', [
            'title' => $document['title'],
            'workspace' => $req->workspace,
            'document' => $document,
            'canWrite' => WorkspaceContext::can('write'),
            'canManage' => WorkspaceContext::can('manage'),
            'currentUserId' => Auth::id(),
            'comments' => Comment::listForDocument((int) $document['id']),
            'comment_count' => Comment::countForDocument((int) $document['id']),
        ]);
    }

    public function showCreate(Request $req): Response
    {
        if (!WorkspaceContext::can('write')) {
            return (new Response())->html('You do not have permission to edit documents', 403);
        }

        return View::render('document/create', [
            'title' => 'New document',
            'workspace' => $req->workspace,
        ]);
    }

    public function create(Request $req): Response
    {
        if (!WorkspaceContext::can('write')) {
            return (new Response())->html('You do not have permission to edit documents', 403);
        }

        $body = $this->body($req);
        if (!$this->validCsrf($req, $body)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $title = is_string($body['title'] ?? null) ? trim($body['title']) : '';
        $content = is_string($body['content'] ?? null) ? $body['content'] : '';
        if (mb_strlen($title) < 1 || mb_strlen($title) > 200) {
            Session::flash('error', 'Document title must be between 1 and 200 characters');

            return (new Response())->redirect('/w/' . $req->param('slug') . '/documents/create');
        }

        $document = Document::create(
            (int) $req->workspace['id'],
            (int) Auth::id(),
            $title,
            $content,
        );

        return (new Response())->redirect('/w/' . $req->param('slug') . '/documents/' . $document['id']);
    }

    public function update(Request $req): Response
    {
        if (!WorkspaceContext::can('write')) {
            return (new Response())->html('You do not have permission to edit documents', 403);
        }

        $body = $this->body($req);
        if (!$this->validCsrf($req, $body)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $document = $this->scopedDocument($req);
        if ($document === null) {
            return (new Response())->html('Document not found', 404);
        }

        $title = array_key_exists('title', $body)
            ? (is_string($body['title']) ? trim($body['title']) : '')
            : (string) $document['title'];
        $content = is_string($body['content'] ?? null) ? $body['content'] : null;
        if (mb_strlen($title) < 1 || mb_strlen($title) > 200 || $content === null) {
            return (new Response())->html('A valid title and content are required', 422);
        }

        $updated = Document::update((int) $document['id'], (int) Auth::id(), $title, $content);
        if ($this->expectsJson($req)) {
            return (new Response())->json([
                'updated_at' => $updated['updated_at'],
                'version_id' => $updated['version_id'],
            ]);
        }

        return (new Response())->redirect('/w/' . $req->param('slug') . '/documents/' . $document['id']);
    }

    public function delete(Request $req): Response
    {
        if (!WorkspaceContext::can('write')) {
            return (new Response())->html('You do not have permission to edit documents', 403);
        }

        $body = $this->body($req);
        if (!$this->validCsrf($req, $body)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $document = $this->scopedDocument($req);
        if ($document === null) {
            return (new Response())->html('Document not found', 404);
        }

        Document::delete((int) $document['id']);
        Session::flash('success', 'Document deleted');

        return (new Response())->redirect('/w/' . $req->param('slug') . '/documents');
    }

    public function history(Request $req): Response
    {
        $document = $this->scopedDocument($req);
        if ($document === null) {
            return (new Response())->html('Document not found', 404);
        }

        return View::render('document/history', [
            'title' => 'Document history',
            'workspace' => $req->workspace,
            'document' => $document,
            'versions' => DocumentVersion::listForDocument((int) $document['id']),
            'canWrite' => WorkspaceContext::can('write'),
        ]);
    }

    public function showVersion(Request $req): Response
    {
        $version = $this->scopedVersion($req);
        if ($version === null) {
            return (new Response())->html('Version not found', 404);
        }

        return View::render('document/version', [
            'title' => 'Document version',
            'workspace' => $req->workspace,
            'document' => $this->scopedDocumentById($req, (int) $version['document_id']),
            'version' => $version,
            'canWrite' => WorkspaceContext::can('write'),
        ]);
    }

    public function restoreVersion(Request $req): Response
    {
        if (!WorkspaceContext::can('write')) {
            return (new Response())->html('You do not have permission to edit documents', 403);
        }

        $body = $this->body($req);
        if (!$this->validCsrf($req, $body)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $version = $this->scopedVersion($req);
        if ($version === null) {
            return (new Response())->html('Version not found', 404);
        }

        Document::update(
            (int) $version['document_id'],
            (int) Auth::id(),
            (string) $version['title'],
            (string) $version['content'],
        );
        Session::flash('success', 'Version restored');

        return (new Response())->redirect('/w/' . $req->param('slug') . '/documents/' . $version['document_id']);
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

        $headerToken = null;
        foreach ($req->headers as $name => $value) {
            if (strtolower($name) === 'x-csrf-token') {
                $headerToken = $value;
                break;
            }
        }

        return $headerToken !== null && CSRF::verify($headerToken);
    }

    private function expectsJson(Request $req): bool
    {
        foreach ($req->headers as $name => $value) {
            $lowerName = strtolower($name);
            if ($lowerName === 'accept' && str_contains(strtolower($value), 'application/json')) {
                return true;
            }
            if ($lowerName === 'x-requested-with' && $value !== '') {
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

        return $this->scopedDocumentById($req, (int) $id);
    }

    private function scopedDocumentById(Request $req, int $id): ?array
    {
        $document = Document::findById($id);
        if ($document === null || (int) $document['workspace_id'] !== (int) $req->workspace['id']) {
            return null;
        }

        return $document;
    }

    private function scopedVersion(Request $req): ?array
    {
        $versionId = $req->param('version_id');
        if ($versionId === null || !ctype_digit($versionId)) {
            return null;
        }

        $version = DocumentVersion::findById((int) $versionId);
        if ($version === null || $this->scopedDocumentById($req, (int) $version['document_id']) === null) {
            return null;
        }

        return $version;
    }
}