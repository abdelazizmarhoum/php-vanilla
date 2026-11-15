<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    private function __construct()
    {
    }

    public static function findById(int $id): ?array
    {
        $statement = Database::q(
            'SELECT * FROM users WHERE id = :id',
            ['id' => $id],
        );
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    public static function findByEmail(string $email): ?array
    {
        $statement = Database::q(
            'SELECT * FROM users WHERE email = :email',
            ['email' => $email],
        );
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    public static function create(string $name, string $email, string $passwordHash): array
    {
        $statement = Database::q(
            <<<'SQL'
            INSERT INTO users (name, email, password_hash)
            VALUES (:name, :email, :password_hash)
            RETURNING *
            SQL,
            [
                'name' => $name,
                'email' => $email,
                'password_hash' => $passwordHash,
            ],
        );
        $user = $statement->fetch();

        if (!is_array($user)) {
            throw new \RuntimeException('User creation returned no row.');
        }

        return $user;
    }

    public static function updatePasswordHash(int $id, string $hash): void
    {
        Database::q(
            'UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id',
            [
                'password_hash' => $hash,
                'id' => $id,
            ],
        );
    }

    public static function markEmailVerified(int $id): void
    {
        Database::q(
            'UPDATE users SET email_verified_at = NOW(), updated_at = NOW() WHERE id = :id',
            ['id' => $id],
        );
    }

    public static function all(): array
    {
        return Database::q('SELECT * FROM users ORDER BY id')->fetchAll();
    }
}
