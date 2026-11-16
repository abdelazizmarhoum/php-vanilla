<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Invitation
{
    private function __construct()
    {
    }

    public static function findPendingByWorkspaceAndEmail(int $workspaceId, string $email): ?array
    {
        $row = Database::q(
            'SELECT * FROM invitations WHERE workspace_id = :workspace_id AND email = :email AND accepted_at IS NULL',
            ['workspace_id' => $workspaceId, 'email' => $email],
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public static function findByTokenHash(string $tokenHash): ?array
    {
        $row = Database::q(
            <<<'SQL'
            SELECT i.*, w.name AS workspace_name, w.slug AS workspace_slug
            FROM invitations i
            INNER JOIN workspaces w ON w.id = i.workspace_id
            WHERE i.token_hash = :token_hash
            SQL,
            ['token_hash' => $tokenHash],
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public static function findById(int $id): ?array
    {
        $row = Database::q(
            'SELECT * FROM invitations WHERE id = :id',
            ['id' => $id],
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public static function create(
        int $workspaceId,
        string $email,
        string $role,
        string $tokenHash,
        int $invitedBy,
        string $expiresAt,
    ): array {
        $row = Database::q(
            'INSERT INTO invitations (workspace_id, email, role, token_hash, invited_by, expires_at) VALUES (:workspace_id, :email, :role, :token_hash, :invited_by, :expires_at) RETURNING *',
            [
                'workspace_id' => $workspaceId,
                'email' => $email,
                'role' => $role,
                'token_hash' => $tokenHash,
                'invited_by' => $invitedBy,
                'expires_at' => $expiresAt,
            ],
        )->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Invitation creation returned no row.');
        }

        return $row;
    }

    public static function delete(int $id): void
    {
        Database::q('DELETE FROM invitations WHERE id = :id', ['id' => $id]);
    }

    public static function markAccepted(int $id): void
    {
        Database::q(
            'UPDATE invitations SET accepted_at = NOW() WHERE id = :id',
            ['id' => $id],
        );
    }

    public static function listPendingForWorkspace(int $workspaceId): array
    {
        return Database::q(
            <<<'SQL'
            SELECT i.id, i.email, i.role, i.expires_at, i.created_at,
                   u.name AS inviter_name
            FROM invitations i
            INNER JOIN users u ON u.id = i.invited_by
            WHERE i.workspace_id = :workspace_id
              AND i.accepted_at IS NULL
              AND i.expires_at > NOW()
            ORDER BY i.created_at DESC
            SQL,
            ['workspace_id' => $workspaceId],
        )->fetchAll();
    }
}
