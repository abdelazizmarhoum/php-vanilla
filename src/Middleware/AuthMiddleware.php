<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Auth\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

final class AuthMiddleware
{
    public function handle(Request $req, callable $next): Response
    {
        if (!Auth::check()) {
            if (str_starts_with($req->path, '/api/')) {
                return (new Response())->json(['error' => 'Unauthenticated'], 401);
            }

            Session::flash('error', 'Please sign in to continue');

            return (new Response())->redirect('/login');
        }

        return $next($req);
    }
}
