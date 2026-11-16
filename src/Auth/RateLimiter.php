<?php
declare(strict_types=1);

namespace App\Auth;

use App\Core\Database;

final class RateLimiter
{
    private function __construct()
    {
    }

    public static function recordAttempt(string $email, ?string $ip, bool $success): void
    {
        Database::q(
            'INSERT INTO login_attempts (email, ip, success) VALUES (:email, :ip, :success)',
            ['email' => $email, 'ip' => $ip, 'success' => $success ? 1 : 0],
        );
    }

    public static function tooManyAttempts(string $email, ?string $ip): bool
    {
        $row = Database::q(
            <<<'SQL'
            SELECT COUNT(*) AS failures
            FROM login_attempts
            WHERE email = :email
              AND (ip = :ip OR :ip IS NULL)
              AND success = FALSE
              AND created_at > NOW() - INTERVAL '15 minutes'
            SQL,
            ['email' => $email, 'ip' => $ip],
        )->fetch();

        return (int) ($row['failures'] ?? 0) >= 5;
    }
}
