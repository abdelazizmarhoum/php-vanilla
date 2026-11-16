<?php
declare(strict_types=1);

namespace App\Core;

final class Session
{
    private function __construct()
    {
    }

    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => (int) config('session.lifetime', 0),
            'path' => '/',
            'secure' => (bool) config('session.secure', false),
            'httponly' => (bool) config('session.httponly', true),
            'samesite' => (string) config('session.samesite', 'Lax'),
        ]);

        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name((string) config('session.name', 'app_session'));
        session_start();

        $lastSeen = (int) ($_SESSION['_last_seen'] ?? 0);
        if ($lastSeen > 0 && (time() - $lastSeen) > (30 * 60)) {
            self::destroy();
            self::start();

            return;
        }
        $_SESSION['_last_seen'] = time();
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();

        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        self::start();

        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::start();

        if (
            !isset($_SESSION['_flash'])
            || !is_array($_SESSION['_flash'])
            || !array_key_exists($key, $_SESSION['_flash'])
        ) {
            return $default;
        }

        $value = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);

        if ($_SESSION['_flash'] === []) {
            unset($_SESSION['_flash']);
        }

        return $value;
    }

    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            session_unset();
            session_destroy();
            session_write_close();
        }

        $_SESSION = [];
    }
}
