<?php
declare(strict_types=1);

namespace App\Auth;

use App\Core\CSRF;
use App\Core\Session;
use App\Models\User;
use InvalidArgumentException;

final class Auth
{
    private function __construct()
    {
    }

    public static function register(string $name, string $email, string $password): array
    {
        $name = trim($name);
        $email = trim($email);

        if ($name === '') {
            throw new InvalidArgumentException('Name is required');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Enter a valid email address');
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters');
        }

        if (User::findByEmail($email) !== null) {
            throw new InvalidArgumentException('Email already registered');
        }

        $algorithm = in_array('argon2id', password_algos(), true)
            ? PASSWORD_ARGON2ID
            : PASSWORD_DEFAULT;
        $passwordHash = password_hash($password, $algorithm);

        if ($passwordHash === false) {
            throw new \RuntimeException('Unable to secure password.');
        }

        return User::create($name, $email, $passwordHash);
    }

    public static function login(string $email, string $password): ?array
    {
        $user = User::findByEmail($email);
        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            return null;
        }

        $algorithm = in_array('argon2id', password_algos(), true)
            ? PASSWORD_ARGON2ID
            : PASSWORD_DEFAULT;
        if (password_needs_rehash((string) $user['password_hash'], $algorithm)) {
            $passwordHash = password_hash($password, $algorithm);
            if ($passwordHash === false) {
                throw new \RuntimeException('Unable to refresh password security.');
            }

            User::updatePasswordHash((int) $user['id'], $passwordHash);
            $user['password_hash'] = $passwordHash;
        }

        Session::regenerate();
        CSRF::rotate();
        Session::set('user_id', (int) $user['id']);

        return $user;
    }

    public static function logout(): void
    {
        Session::remove('user_id');
        Session::regenerate();
        Session::destroy();
    }

    public static function user(): ?array
    {
        static $cached = false;
        static $cachedUser = null;

        if ($cached) {
            return $cachedUser;
        }

        $cached = true;
        $id = Session::get('user_id');
        if (!is_int($id) && !is_string($id) && !is_numeric($id)) {
            return null;
        }

        $cachedUser = User::findById((int) $id);

        return $cachedUser;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $user = self::user();

        return $user !== null ? (int) $user['id'] : null;
    }
}
