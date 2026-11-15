<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Auth\Auth;
use App\Auth\WorkspaceContext;
use App\Core\Request;
use App\Core\Response;
use App\Models\Membership;
use App\Models\Workspace;

final class WorkspaceMiddleware
{
    public function handle(Request $req, callable $next): Response
    {
        $slug = $req->param('slug');
        if ($slug === null || $slug === '') {
            return (new Response())->html('Workspace not specified', 400);
        }

        $workspace = Workspace::findBySlug($slug);
        if ($workspace === null) {
            return (new Response())->html('Workspace not found', 404);
        }

        $userId = Auth::id();
        if ($userId === null) {
            return (new Response())->html('Unauthenticated', 401);
        }

        $membership = Membership::find((int) $workspace['id'], $userId);
        if ($membership === null) {
            return (new Response())->html('You do not have access to this workspace', 403);
        }

        WorkspaceContext::set($workspace, $membership);
        $req->setWorkspace($workspace);
        $req->setMembership($membership);

        return $next($req);
    }
}