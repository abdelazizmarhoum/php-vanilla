<?php
declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\View;

return static function (Router $router): void {
    $router->get('/', static function (Request $request, Response $response): Response {
        return $response->html('<h1>Home</h1><p>Welcome to the team workspace.</p>');
    });

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
        $token = $_POST['_token'] ?? null;
        if (!is_string($token) || !CSRF::verify($token)) {
            return $response->html('CSRF check failed', 419);
        }

        $message = $_POST['message'] ?? '';
        Session::set('_form_last', is_string($message) ? $message : '');
        Session::flash('success', 'Form submitted');

        return $response->redirect('/form');
    });
};
