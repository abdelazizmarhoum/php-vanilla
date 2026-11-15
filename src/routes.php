<?php
declare(strict_types=1);

use App\Auth\Auth;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Core\CSRF;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Middleware\AuthMiddleware;

return static function (Router $router): void {
    $router->get('/', static function (Request $request, Response $response): Response {
        if (Auth::check()) {
            return $response->redirect('/dashboard');
        }

        return $response->redirect('/login');
    });

    $router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);

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
