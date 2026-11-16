<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class RememberToken
{
    private function __construct()
    {
    }

    public static function create(
        int $userId,
        string $selector,
        string $validatorHash,
        string $expiresAt,
    ): array {
        $row = Database::q(
            'INSERT INTO remember_tokens (user_id, selector, validator_hash, expires_at) VALUES (:user_id, :selector, :validator_hash, :expires_at) RETURNING *',
            [
                'user_id' => $userId,
                'selector' => $selector,
                'validator_hash' => $validatorHash,
                'expires_at' => $expiresAt,
            ],
        )->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Remember token creation returned no row.');
        }

        return $row;
    }

    public static function findBySelector(string $selector): ?array
    {
        $row = Database::q(
            'SELECT * FROM remember_tokens WHERE selector = :selector',
            ['selector' => $selector],
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public static function deleteBySelector(string $selector): void
    {
        Database::q('DELETE FROM remember_tokens WHERE selector = :selector', ['selector' => $selector]);
    }

    public static function deleteForUser(int $userId): void
    {
        Database::q('DELETE FROM remember_tokens WHERE user_id = :user_id', ['user_id' => $userId]);
    }
}
