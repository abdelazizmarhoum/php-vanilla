<?php
declare(strict_types=1);

namespace App\Core;

use Closure;
use LogicException;

final class Router
{
    /**
     * @var array<int, array{
     *     method: string,
     *     path: string,
     *     pattern: string,
     *     params: array<int, string>,
     *     handler: Closure|array,
     *     middleware: array<int, string>
     * }>
     */
    private array $routes = [];

    /**
     * @param Closure|array $handler
     * @param array<int, string> $middleware
     */
    public function get(string $path, Closure|array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    /**
     * @param Closure|array $handler
     * @param array<int, string> $middleware
     */
    public function post(string $path, Closure|array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    /**
     * @param Closure|array $handler
     * @param array<int, string> $middleware
     */
    public function put(string $path, Closure|array $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    /**
     * @param Closure|array $handler
     * @param array<int, string> $middleware
     */
    public function delete(string $path, Closure|array $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    public function dispatch(string $method, string $path): void
    {
        try {
            $this->dispatchRoute($method, $path);
        } catch (\Throwable $exception) {
            $logPath = dirname(__DIR__, 2) . '/storage/logs/app.log';
            $message = sprintf(
                "[%s] %s in %s:%d%s",
                date('c'),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                PHP_EOL,
            );
            file_put_contents($logPath, $message, FILE_APPEND | LOCK_EX);
            $details = (bool) config('app.debug', false) ? '<pre>' . e($exception->getMessage()) . '</pre>' : '';
            try {
                View::render('errors/500', [
                    'title' => 'Server error',
                    'details' => $details,
                ])->status(500)->send();
            } catch (\Throwable $renderException) {
                (new Response())->html('Something went wrong', 500)->send();
            }
        }
    }

    private function dispatchRoute(string $method, string $path): void
    {
        $request = $this->createRequest($method, $path);

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            $matches = [];
            if (preg_match($route['pattern'], $request->path, $matches) !== 1) {
                continue;
            }

            $params = [];
            foreach ($route['params'] as $index => $name) {
                $params[$name] = rawurldecode($matches[$index + 1]);
            }

            $request->setParams($params);
            $response = $this->runMiddleware(
                $route['middleware'],
                0,
                $request,
                $route['handler'],
                $params,
            );
            $response->send();

            return;
        }

        View::render('errors/404', ['title' => 'Not found'])->status(404)->send();
    }

    /**
     * @param Closure|array $handler
     * @param array<int, string> $middleware
     */
    private function add(
        string $method,
        string $path,
        Closure|array $handler,
        array $middleware,
    ): void {
        [$pattern, $params] = $this->compilePath($path);

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'params' => $params,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    /**
     * @return array{0: string, 1: array<int, string>}
     */
    private function compilePath(string $path): array
    {
        if ($path === '/') {
            return ['#^/$#', []];
        }

        $segments = explode('/', trim($path, '/'));
        $pattern = '';
        $params = [];

        foreach ($segments as $segment) {
            $pattern .= '/';

            if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)\}$/', $segment, $match) === 1) {
                $params[] = $match[1];
                $pattern .= '([^/]+)';
                continue;
            }

            $pattern .= preg_quote($segment, '#');
        }

        return ['#^' . $pattern . '/?$#', $params];
    }

    private function createRequest(string $method, string $path): Request
    {
        $query = is_array($_GET) ? $_GET : [];
        $headers = $this->requestHeaders();
        $body = $this->requestBody($method, $headers);

        return new Request(
            strtoupper($method),
            parse_url($path, PHP_URL_PATH) ?: '/',
            $query,
            $body,
            $headers,
        );
    }

    /**
     * @param array<string, string> $headers
     */
    private function requestBody(string $method, array $headers): array|string
    {
        if (in_array(strtoupper($method), ['GET', 'HEAD'], true)) {
            return [];
        }

        $rawBody = file_get_contents('php://input') ?: '';
        if ($rawBody === '') {
            return is_array($_POST) ? $_POST : [];
        }

        $contentType = '';
        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'content-type') {
                $contentType = strtolower($value);
                break;
            }
        }

        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : $rawBody;
        }

        if (str_contains($contentType, 'application/x-www-form-urlencoded')) {
            parse_str($rawBody, $parsed);

            return $parsed;
        }

        if (str_contains($contentType, 'multipart/form-data')) {
            return is_array($_POST) ? $_POST : [];
        }

        return $rawBody;
    }

    /**
     * @return array<string, string>
     */
    private function requestHeaders(): array
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();

            if (is_array($headers)) {
                return array_map(
                    static fn (mixed $value): string => (string) $value,
                    $headers,
                );
            }
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (!str_starts_with($name, 'HTTP_')) {
                continue;
            }

            $headerName = str_replace(
                ' ',
                '-',
                ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))),
            );
            $headers[$headerName] = (string) $value;
        }

        return $headers;
    }

    /**
     * @param array<int, string> $middleware
     * @param Closure|array $handler
     * @param array<string, string> $params
     */
    private function runMiddleware(
        array $middleware,
        int $index,
        Request $request,
        Closure|array $handler,
        array $params,
    ): Response {
        if (!isset($middleware[$index])) {
            return $this->callHandler($handler, $request, $params);
        }

        $middlewareClass = $middleware[$index];
        $middlewareInstance = new $middlewareClass();

        return $middlewareInstance->handle(
            $request,
            fn (Request $nextRequest): Response => $this->runMiddleware(
                $middleware,
                $index + 1,
                $nextRequest,
                $handler,
                $params,
            ),
        );
    }

    /**
     * @param Closure|array $handler
     * @param array<string, string> $params
     */
    private function callHandler(
        Closure|array $handler,
        Request $request,
        array $params,
    ): Response {
        if ($handler instanceof Closure) {
            return $handler($request, new Response());
        }

        if (count($handler) !== 2 || !is_string($handler[0]) || !is_string($handler[1])) {
            throw new LogicException('Invalid route controller handler.');
        }

        $controller = new $handler[0]();
        $method = $handler[1];

        return $controller->{$method}($request, $params);
    }
}
