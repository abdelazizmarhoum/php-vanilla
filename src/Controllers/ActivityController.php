<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\WorkspaceContext;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\ActivityLog;

final class ActivityController
{
    public function feed(Request $req): Response
    {
        $after = $req->query['after'] ?? null;
        $afterId = is_scalar($after) && ctype_digit((string) $after) ? (int) $after : null;
        $workspaceId = (int) WorkspaceContext::workspace()['id'];
        $items = $afterId === null
            ? ActivityLog::listForWorkspace($workspaceId, 30)
            : ActivityLog::listSince($workspaceId, $afterId, 30);

        return (new Response())->json(['items' => $items]);
    }

    public function page(Request $req): Response
    {
        $workspace = WorkspaceContext::workspace();

        return View::render('activity/index', [
            'title' => 'Activity',
            'workspace' => $workspace,
            'activity' => ActivityLog::listForWorkspace((int) $workspace['id'], 50),
        ]);
    }
}
