<?php
declare(strict_types=1);

namespace App\Auth;

use App\Models\RememberToken;
use App\Models\User;

final class RememberMe
{
    private const COOKIE_NAME = 'remember';
    private const LIFETIME = 30 * 86400;

    private function __construct()
    {
    }

    public static function issue(int $userId): void
    {
        $selector = bin2hex(random_bytes(9));
        $validator = bin2hex(random_bytes(32));
        $expiresAt = date('c', time() + self::LIFETIME);
        RememberToken::create($userId, $selector, hash('sha256', $validator), $expiresAt);
        self::setCookie($selector . ':' . $validator, time() + self::LIFETIME);
    }

    public static function attempt(): ?array
    {
        $cookie = $_COOKIE[self::COOKIE_NAME] ?? null;
        if (!is_string($cookie)) {
            return null;
        }

        $parts = explode(':', $cookie);
        if (count($parts) !== 2 || !ctype_xdigit($parts[0]) || !ctype_xdigit($parts[1])) {
            self::clear();

            return null;
        }

        [$selector, $validator] = $parts;
        $row = RememberToken::findBySelector($selector);
        if ($row === null) {
            self::clear();

            return null;
        }
        if (strtotime((string) $row['expires_at']) < time()) {
            RememberToken::deleteBySelector($selector);
            self::clear();

            return null;
        }
        if (!hash_equals((string) $row['validator_hash'], hash('sha256', $validator))) {
            RememberToken::deleteBySelector($selector);
            self::clear();

            return null;
        }

        $user = User::findById((int) $row['user_id']);
        if ($user === null) {
            RememberToken::deleteBySelector($selector);
            self::clear();

            return null;
        }

        RememberToken::deleteBySelector($selector);
        self::issue((int) $user['id']);

        return $user;
    }

    public static function clear(): void
    {
        $cookie = $_COOKIE[self::COOKIE_NAME] ?? null;
        if (is_string($cookie)) {
            $parts = explode(':', $cookie, 2);
            if ($parts[0] !== '') {
                RememberToken::deleteBySelector($parts[0]);
            }
        }

        self::setCookie('', time() - 3600);
    }

    private static function setCookie(string $value, int $expires): void
    {
        setcookie(self::COOKIE_NAME, $value, [
            'expires' => $expires,
            'path' => '/',
            'domain' => '',
            'secure' => (bool) config('session.secure', false),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
