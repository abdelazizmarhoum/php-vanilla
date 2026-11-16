<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class PasswordReset
{
    private function __construct()
    {
    }

    public static function create(int $userId, string $tokenHash, string $expiresAt): array
    {
        $row = Database::q(
            'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at) RETURNING *',
            ['user_id' => $userId, 'token_hash' => $tokenHash, 'expires_at' => $expiresAt],
        )->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Password reset creation returned no row.');
        }

        return $row;
    }

    public static function findByTokenHash(string $tokenHash): ?array
    {
        $row = Database::q(
            'SELECT * FROM password_resets WHERE token_hash = :token_hash',
            ['token_hash' => $tokenHash],
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public static function markUsed(int $id): void
    {
        Database::q('UPDATE password_resets SET used_at = NOW() WHERE id = :id', ['id' => $id]);
    }

    public static function deleteForUser(int $userId): void
    {
        Database::q('DELETE FROM password_resets WHERE user_id = :user_id', ['user_id' => $userId]);
    }
}
