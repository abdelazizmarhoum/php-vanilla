<?php
declare(strict_types=1);

use App\Auth\Auth;
use App\Controllers\ActivityController;
use App\Controllers\AuthController;
use App\Controllers\CommentController;
use App\Controllers\DashboardController;
use App\Controllers\DocumentController;
use App\Controllers\EmailVerificationController;
use App\Controllers\InvitationController;
use App\Controllers\PasswordResetController;
use App\Controllers\WorkspaceController;
use App\Controllers\SearchController;
use App\Core\CSRF;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Middleware\AuthMiddleware;
use App\Middleware\WorkspaceMiddleware;

return static function (Router $router): void {
    $router->get('/', static function (Request $request, Response $response): Response {
        if (Auth::check()) {
            return $response->redirect('/dashboard');
        }

        return $response->redirect('/login');
    });

    $router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/workspaces', [WorkspaceController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/workspaces/create', [WorkspaceController::class, 'showCreate'], [AuthMiddleware::class]);
    $router->post('/workspaces', [WorkspaceController::class, 'create'], [AuthMiddleware::class]);
    $router->get('/w/{slug}', [WorkspaceController::class, 'show'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->get('/w/{slug}/settings', [WorkspaceController::class, 'showSettings'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->post('/w/{slug}/settings', [WorkspaceController::class, 'update'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->post('/w/{slug}/delete', [WorkspaceController::class, 'delete'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->get('/w/{slug}/invite', [InvitationController::class, 'showInvite'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->post('/w/{slug}/invite', [InvitationController::class, 'send'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->post('/w/{slug}/invite/{id}/revoke', [InvitationController::class, 'revoke'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->get('/w/{slug}/documents', [DocumentController::class, 'index'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->get('/w/{slug}/documents/create', [DocumentController::class, 'showCreate'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->post('/w/{slug}/documents', [DocumentController::class, 'create'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->get('/w/{slug}/documents/{id}', [DocumentController::class, 'show'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->post('/w/{slug}/documents/{id}/update', [DocumentController::class, 'update'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->post('/w/{slug}/documents/{id}/delete', [DocumentController::class, 'delete'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->post('/w/{slug}/documents/{id}/comments', [CommentController::class, 'create'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->post('/w/{slug}/documents/{id}/comments/{comment_id}/delete', [CommentController::class, 'delete'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->get('/w/{slug}/documents/{id}/history', [DocumentController::class, 'history'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->get('/w/{slug}/documents/{id}/history/{version_id}', [DocumentController::class, 'showVersion'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->post('/w/{slug}/documents/{id}/history/{version_id}/restore', [DocumentController::class, 'restoreVersion'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->get('/w/{slug}/activity', [ActivityController::class, 'page'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->get('/w/{slug}/activity/feed', [ActivityController::class, 'feed'], [AuthMiddleware::class, WorkspaceMiddleware::class]);
    $router->get('/search', [SearchController::class, 'page'], [AuthMiddleware::class]);
    $router->get('/search/api', [SearchController::class, 'api'], [AuthMiddleware::class]);
    $router->get('/invitations/{token}', [InvitationController::class, 'showAccept']);
    $router->post('/invitations/{token}', [InvitationController::class, 'accept'], [AuthMiddleware::class]);
    $router->get('/verify-email/{token}', [EmailVerificationController::class, 'verify']);
    $router->post('/verify-email/resend', [EmailVerificationController::class, 'resend'], [AuthMiddleware::class]);
    $router->get('/forgot-password', [PasswordResetController::class, 'showRequest']);
    $router->post('/forgot-password', [PasswordResetController::class, 'request']);
    $router->get('/reset-password/{token}', [PasswordResetController::class, 'showReset']);
    $router->post('/reset-password/{token}', [PasswordResetController::class, 'reset']);

    $router->get('/about', static function (Request $request, Response $response): Response {
        return View::render('pages/about', ['title' => 'About']);
    });

    $router->get('/users/{id}', static function (Request $request, Response $response): Response {
        $id = e($request->param('id'));

        return $response->html('<h1>User</h1><p>User ID: ' . $id . '</p>');
    });

    $router->get('/form', static function (Request $request, Response $response): Response {
        return View::render('pages/form', ['title' => 'CSRF Demo']);
    });

    $router->post('/form', static function (Request $request, Response $response): Response {
        $body = is_array($request->body) ? $request->body : [];
        $token = $body['_token'] ?? null;
        if (!is_string($token) || !CSRF::verify($token)) {
            return $response->html('CSRF check failed', 419);
        }

        $message = $body['message'] ?? '';
        Session::set('_form_last', is_string($message) ? $message : '');
        Session::flash('success', 'Form submitted');

        return $response->redirect('/form');
    });

    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);
    $router->post('/logout', [AuthController::class, 'logout']);
};