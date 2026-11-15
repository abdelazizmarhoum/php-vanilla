<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Auth\WorkspaceContext;
use App\Core\CSRF;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Membership;
use App\Models\Workspace;
use InvalidArgumentException;

final class WorkspaceController
{
    public function index(Request $req): Response
    {
        return View::render('workspace/index', [
            'title' => 'Workspaces',
            'workspaces' => Workspace::allForUser((int) Auth::id()),
        ]);
    }

    public function showCreate(Request $req): Response
    {
        return View::render('workspace/create', ['title' => 'Create workspace']);
    }

    public function create(Request $req): Response
    {
        $body = is_array($req->body) ? $req->body : [];
        if (!self::validCsrf($body)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $name = is_string($body['name'] ?? null) ? trim($body['name']) : '';
        if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
            Session::flash('error', 'Workspace name must be between 2 and 80 characters');

            return (new Response())->redirect('/workspaces/create');
        }

        $slug = self::uniqueSlug($name);
        $ownerId = Auth::id();
        if ($ownerId === null) {
            return (new Response())->redirect('/login');
        }

        $pdo = Database::pdo();
        try {
            $pdo->beginTransaction();
            $workspace = Workspace::create($ownerId, $name, $slug);
            Membership::add((int) $workspace['id'], $ownerId, 'owner');
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        Session::flash('success', 'Workspace created');

        return (new Response())->redirect('/w/' . $slug);
    }

    public function show(Request $req): Response
    {
        $workspace = WorkspaceContext::workspace();
        $membership = WorkspaceContext::membership();

        return View::render('workspace/show', [
            'title' => $workspace['name'] ?? 'Workspace',
            'workspace' => $workspace,
            'membership' => $membership,
            'role' => WorkspaceContext::role(),
            'memberCount' => Membership::count((int) $workspace['id']),
        ]);
    }

    public function showSettings(Request $req): Response
    {
        if (!WorkspaceContext::can('manage')) {
            return (new Response())->html('You do not have permission to manage this workspace', 403);
        }

        return View::render('workspace/settings', [
            'title' => 'Workspace settings',
            'workspace' => WorkspaceContext::workspace(),
            'members' => Membership::listForWorkspace((int) WorkspaceContext::workspace()['id']),
        ]);
    }

    public function update(Request $req): Response
    {
        if (!WorkspaceContext::can('manage')) {
            return (new Response())->html('You do not have permission to manage this workspace', 403);
        }

        $body = is_array($req->body) ? $req->body : [];
        if (!self::validCsrf($body)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $workspace = WorkspaceContext::workspace();
        $name = is_string($body['name'] ?? null) ? trim($body['name']) : '';
        $slug = is_string($body['slug'] ?? null) ? slugify($body['slug']) : '';
        if ($workspace === null || mb_strlen($name) < 2 || mb_strlen($name) > 80 || $slug === '') {
            Session::flash('error', 'Enter a valid workspace name and slug');

            return (new Response())->redirect('/w/' . (string) ($workspace['slug'] ?? ''));
        }

        $existing = Workspace::findBySlug($slug);
        if ($existing !== null && (int) $existing['id'] !== (int) $workspace['id']) {
            Session::flash('error', 'That workspace slug is already in use');

            return (new Response())->redirect('/w/' . $workspace['slug'] . '/settings');
        }

        Workspace::update((int) $workspace['id'], $name, $slug);
        Session::flash('success', 'Workspace updated');

        return (new Response())->redirect('/w/' . $slug);
    }

    public function delete(Request $req): Response
    {
        if (!WorkspaceContext::can('delete')) {
            return (new Response())->html('You do not have permission to delete this workspace', 403);
        }

        $body = is_array($req->body) ? $req->body : [];
        if (!self::validCsrf($body)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $workspace = WorkspaceContext::workspace();
        if ($workspace !== null) {
            Workspace::delete((int) $workspace['id']);
        }
        Session::flash('success', 'Workspace deleted');

        return (new Response())->redirect('/dashboard');
    }

    /** @param array<string, mixed> $body */
    private static function validCsrf(array $body): bool
    {
        $token = $body['_token'] ?? null;

        return is_string($token) && CSRF::verify($token);
    }

    private static function uniqueSlug(string $name): string
    {
        $base = slugify($name);
        if ($base === '') {
            throw new InvalidArgumentException('Workspace name must contain letters or numbers');
        }

        $slug = $base;
        while (Workspace::findBySlug($slug) !== null) {
            $slug = $base . '-' . bin2hex(random_bytes(2));
        }

        return $slug;
    }
}