<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Membership
{
    private const ROLE_RANKS = [
        'owner' => 4,
        'admin' => 3,
        'member' => 2,
        'viewer' => 1,
    ];

    private function __construct()
    {
    }

    public static function find(int $workspaceId, int $userId): ?array
    {
        $row = Database::q(
            'SELECT * FROM memberships WHERE workspace_id = :workspace_id AND user_id = :user_id',
            ['workspace_id' => $workspaceId, 'user_id' => $userId],
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public static function add(int $workspaceId, int $userId, string $role): array
    {
        $row = Database::q(
            'INSERT INTO memberships (workspace_id, user_id, role) VALUES (:workspace_id, :user_id, :role) RETURNING *',
            ['workspace_id' => $workspaceId, 'user_id' => $userId, 'role' => $role],
        )->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Membership creation returned no row.');
        }

        return $row;
    }

    public static function updateRole(int $workspaceId, int $userId, string $role): void
    {
        Database::q(
            'UPDATE memberships SET role = :role WHERE workspace_id = :workspace_id AND user_id = :user_id',
            ['role' => $role, 'workspace_id' => $workspaceId, 'user_id' => $userId],
        );
    }

    public static function remove(int $workspaceId, int $userId): void
    {
        Database::q(
            'DELETE FROM memberships WHERE workspace_id = :workspace_id AND user_id = :user_id',
            ['workspace_id' => $workspaceId, 'user_id' => $userId],
        );
    }

    public static function listForWorkspace(int $workspaceId): array
    {
        return Database::q(
            <<<'SQL'
            SELECT u.id AS user_id, u.name, u.email, m.role, m.created_at
            FROM memberships m
            INNER JOIN users u ON u.id = m.user_id
            WHERE m.workspace_id = :workspace_id
            ORDER BY m.created_at, u.name
            SQL,
            ['workspace_id' => $workspaceId],
        )->fetchAll();
    }

    public static function count(int $workspaceId): int
    {
        $row = Database::q(
            'SELECT COUNT(*) AS count FROM memberships WHERE workspace_id = :workspace_id',
            ['workspace_id' => $workspaceId],
        )->fetch();

        return (int) ($row['count'] ?? 0);
    }

    public static function rank(string $role): int
    {
        return self::ROLE_RANKS[$role] ?? 0;
    }
}