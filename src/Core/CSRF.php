<?php
declare(strict_types=1);

namespace App\Core;

final class CSRF
{
    private const SESSION_KEY = '_csrf_token';

    private function __construct()
    {
    }

    public static function token(): string
    {
        if (!Session::has('_csrf_issued_at')) {
            Session::set('_csrf_issued_at', time());
        }

        $age = time() - (int) Session::get('_csrf_issued_at', 0);
        if ($age > 4 * 3600 || !Session::has(self::SESSION_KEY)) {
            self::rotate();
            Session::set('_csrf_issued_at', time());
        }

        return (string) Session::get(self::SESSION_KEY);
    }

    public static function verify(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        $sessionToken = Session::get(self::SESSION_KEY);
        if (!is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, (string) $token);
    }

    public static function rotate(): void
    {
        Session::set(self::SESSION_KEY, bin2hex(random_bytes(32)));
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(self::token()) . '">';
    }
}
