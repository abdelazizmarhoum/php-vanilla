<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\WorkspaceContext;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

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
        ]);
    }
}