<?php
declare(strict_types=1);

use App\Core\App;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function config(string $key, mixed $default = null): mixed
{
    return App::config($key, $default);
}

function url(string $path = ''): string
{
    $base = (string) config('app.url', '');
    if ($path === '') return $base;
    return $base . '/' . ltrim($path, '/');
}