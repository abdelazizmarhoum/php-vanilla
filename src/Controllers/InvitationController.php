<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Auth\InvitationAcceptor;
use App\Auth\InvitationService;
use App\Auth\WorkspaceContext;
use App\Core\CSRF;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Invitation;
use InvalidArgumentException;
use RuntimeException;

final class InvitationController
{
    public function showInvite(Request $req): Response
    {
        if (!WorkspaceContext::can('manage')) {
            return (new Response())->html('You do not have permission to manage this workspace', 403);
        }

        return View::render('workspace/invite', [
            'title' => 'Invite members',
            'workspace' => WorkspaceContext::workspace(),
            'pending' => Invitation::listPendingForWorkspace((int) WorkspaceContext::workspace()['id']),
        ]);
    }

    public function send(Request $req): Response
    {
        if (!WorkspaceContext::can('manage')) {
            return (new Response())->html('You do not have permission to manage this workspace', 403);
        }

        $body = is_array($req->body) ? $req->body : [];
        if (!$this->validCsrf($body)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $email = is_string($body['email'] ?? null) ? $body['email'] : '';
        $role = is_string($body['role'] ?? null) ? $body['role'] : '';
        try {
            $result = InvitationService::invite(
                (int) $req->workspace['id'],
                $email,
                $role,
                (int) Auth::id(),
            );
        } catch (InvalidArgumentException $exception) {
            Session::flash('error', $exception->getMessage());

            return (new Response())->redirect('/w/' . $req->param('slug') . '/invite');
        }

        activity((int) $req->workspace['id'], 'member.invited', 'invitation', (int) $result['invitation']['id'], [
            'email' => $email,
            'role' => $role,
        ]);
        Session::flash('success', 'Invitation sent. Check storage/logs/mail.log for the link.');

        return (new Response())->redirect('/w/' . $req->param('slug') . '/invite');
    }

    public function revoke(Request $req): Response
    {
        if (!WorkspaceContext::can('manage')) {
            return (new Response())->html('You do not have permission to manage this workspace', 403);
        }

        $body = is_array($req->body) ? $req->body : [];
        if (!$this->validCsrf($body)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $id = $req->param('id');
        if ($id === null || !ctype_digit($id)) {
            return (new Response())->html('Invitation not found', 404);
        }
        $invitation = Invitation::findById((int) $id);
        if ($invitation === null || (int) $invitation['workspace_id'] !== (int) $req->workspace['id']) {
            return (new Response())->html('Invitation not found', 404);
        }

        Invitation::delete((int) $id);
        Session::flash('success', 'Invitation revoked');

        return (new Response())->redirect('/w/' . $req->param('slug') . '/invite');
    }

    public function showAccept(Request $req): Response
    {
        $token = $req->param('token') ?? '';
        $invitation = InvitationAcceptor::preview($token);
        if ($invitation === null) {
            return View::render('invitation/invalid', ['title' => 'Invalid invitation']);
        }

        $workspace = [
            'name' => $invitation['workspace_name'],
            'slug' => $invitation['workspace_slug'],
        ];
        if (!Auth::check()) {
            return View::render('invitation/register-or-login', [
                'title' => 'Accept invitation',
                'workspace' => $workspace,
                'token' => $token,
            ]);
        }

        $user = Auth::user();
        if ($user === null || strcasecmp((string) $user['email'], (string) $invitation['email']) !== 0) {
            return View::render('invitation/mismatch', [
                'title' => 'Invitation email mismatch',
                'invitation' => $invitation,
                'current_user_email' => $user['email'] ?? '',
            ]);
        }

        return View::render('invitation/accept', [
            'title' => 'Accept invitation',
            'workspace' => $workspace,
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function accept(Request $req): Response
    {
        $body = is_array($req->body) ? $req->body : [];
        if (!$this->validCsrf($body)) {
            return (new Response())->html('CSRF check failed', 419);
        }

        $token = $req->param('token') ?? '';
        try {
            $result = InvitationAcceptor::accept($token, (int) Auth::id());
        } catch (RuntimeException $exception) {
            Session::flash('error', $exception->getMessage());

            return (new Response())->redirect('/invitations/' . $token);
        }

        activity((int) $result['workspace']['id'], 'member.joined', 'membership', null, [
            'user_id' => (int) Auth::id(),
            'role' => $result['role'],
        ]);
        Session::flash('success', 'You joined the workspace');

        return (new Response())->redirect('/w/' . $result['workspace']['slug']);
    }

    /** @param array<string, mixed> $body */
    private function validCsrf(array $body): bool
    {
        $token = $body['_token'] ?? null;

        return is_string($token) && CSRF::verify($token);
    }
}