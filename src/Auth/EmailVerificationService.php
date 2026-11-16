<?php
declare(strict_types=1);

namespace App\Auth;

use App\Models\EmailVerification;
use App\Models\User;
use RuntimeException;

final class EmailVerificationService
{
    private function __construct()
    {
    }

    public static function send(int $userId, string $email): string
    {
        EmailVerification::deleteForUser($userId);
        $rawToken = bin2hex(random_bytes(32));
        $expiresAt = date('c', time() + 86400);
        EmailVerification::create($userId, hash('sha256', $rawToken), $expiresAt);
        $line = sprintf(
            "[VERIFY] %s token=%s expires=%s url=%s%s",
            $email,
            $rawToken,
            $expiresAt,
            url('/verify-email/' . $rawToken),
            PHP_EOL,
        );
        file_put_contents(dirname(__DIR__, 2) . '/storage/logs/mail.log', $line, FILE_APPEND | LOCK_EX);

        return $rawToken;
    }

    public static function verify(string $rawToken): array
    {
        $verification = EmailVerification::findByTokenHash(hash('sha256', $rawToken));
        if (
            $verification === null
            || $verification['used_at'] !== null
            || strtotime((string) $verification['expires_at']) < time()
        ) {
            throw new RuntimeException('This verification link is invalid or has expired.');
        }

        $user = User::findById((int) $verification['user_id']);
        if ($user === null) {
            throw new RuntimeException('The account for this verification link no longer exists.');
        }

        User::markEmailVerified((int) $user['id']);
        EmailVerification::markUsed((int) $verification['id']);

        return ['user' => User::findById((int) $user['id']) ?? $user];
    }
}
