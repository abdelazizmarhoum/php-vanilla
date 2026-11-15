<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Workspace
{
    private function __construct()
    {
    }

    public static function findById(int $id): ?array
    {
        $row = Database::q('SELECT * FROM workspaces WHERE id = :id', ['id' => $id])->fetch();

        return is_array($row) ? $row : null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $row = Database::q('SELECT * FROM workspaces WHERE slug = :slug', ['slug' => $slug])->fetch();

        return is_array($row) ? $row : null;
    }

    public static function create(int $ownerId, string $name, string $slug): array
    {
        $row = Database::q(
            'INSERT INTO workspaces (owner_id, name, slug) VALUES (:owner_id, :name, :slug) RETURNING *',
            ['owner_id' => $ownerId, 'name' => $name, 'slug' => $slug],
        )->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Workspace creation returned no row.');
        }

        return $row;
    }

    public static function update(int $id, string $name, string $slug): void
    {
        Database::q(
            'UPDATE workspaces SET name = :name, slug = :slug, updated_at = NOW() WHERE id = :id',
            ['name' => $name, 'slug' => $slug, 'id' => $id],
        );
    }

    public static function delete(int $id): void
    {
        Database::q('DELETE FROM workspaces WHERE id = :id', ['id' => $id]);
    }

    public static function allForUser(int $userId): array
    {
        return Database::q(
            <<<'SQL'
            SELECT w.*, m.role
            FROM workspaces w
            INNER JOIN memberships m ON m.workspace_id = w.id
            WHERE m.user_id = :user_id
            ORDER BY w.name, w.id
            SQL,
            ['user_id' => $userId],
        )->fetchAll();
    }
}