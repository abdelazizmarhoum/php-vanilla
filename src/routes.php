<?php
declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

return static function (Router $router): void {
    $router->get('/', static function (Request $request, Response $response): Response {
        return $response->html('<h1>Home</h1><p>Welcome to the team workspace.</p>');
    });

    $router->get('/about', static function (Request $request, Response $response): Response {
        return $response->html('<h1>About</h1><p>This is the team workspace application.</p>');
    });

    $router->get('/users/{id}', static function (Request $request, Response $response): Response {
        $id = e($request->param('id'));

        return $response->html('<h1>User</h1><p>User ID: ' . $id . '</p>');
    });
};
