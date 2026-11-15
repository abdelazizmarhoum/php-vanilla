<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Workspace;

final class DashboardController
{
    public function index(Request $req): Response
    {
        return View::render('dashboard/index', [
            'title' => 'Dashboard',
            'user' => Auth::user(),
            'workspaces' => Workspace::allForUser((int) Auth::id()),
        ]);
    }
}