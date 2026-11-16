<?php
declare(strict_types=1);

namespace App\Auth;

use App\Models\PasswordReset;
use App\Models\User;
use InvalidArgumentException;
use RuntimeException;

final class PasswordResetService
{
    private function __construct()
    {
    }

    public static function request(string $email): void
    {
        $user = User::findByEmail(trim($email));
        if ($user === null) {
            return;
        }

        PasswordReset::deleteForUser((int) $user['id']);
        $rawToken = bin2hex(random_bytes(32));
        $expiresAt = date('c', time() + 3600);
        PasswordReset::create((int) $user['id'], hash('sha256', $rawToken), $expiresAt);
        $line = sprintf(
            "[RESET] %s token=%s expires=%s url=%s%s",
            $user['email'],
            $rawToken,
            $expiresAt,
            url('/reset-password/' . $rawToken),
            PHP_EOL,
        );
        file_put_contents(dirname(__DIR__, 2) . '/storage/logs/mail.log', $line, FILE_APPEND | LOCK_EX);
    }

    public static function reset(string $rawToken, string $newPassword): array
    {
        $reset = PasswordReset::findByTokenHash(hash('sha256', $rawToken));
        if (
            $reset === null
            || $reset['used_at'] !== null
            || strtotime((string) $reset['expires_at']) < time()
        ) {
            throw new RuntimeException('This reset link is invalid, expired, or has already been used.');
        }
        if (strlen($newPassword) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters');
        }

        $algorithm = in_array('argon2id', password_algos(), true)
            ? PASSWORD_ARGON2ID
            : PASSWORD_DEFAULT;
        $hash = password_hash($newPassword, $algorithm);
        if ($hash === false) {
            throw new RuntimeException('Unable to secure password.');
        }

        $user = User::findById((int) $reset['user_id']);
        if ($user === null) {
            throw new RuntimeException('The account for this reset link no longer exists.');
        }

        User::updatePasswordHash((int) $user['id'], $hash);
        \App\Models\RememberToken::deleteForUser((int) $user['id']);
        PasswordReset::markUsed((int) $reset['id']);
        // Remember-me token invalidation will be added with remember-me support.

        return ['user' => User::findById((int) $user['id']) ?? $user];
    }
}
