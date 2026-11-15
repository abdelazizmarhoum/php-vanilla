<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ActivityLog
{
    private function __construct()
    {
    }

    public static function log(
        int $workspaceId,
        ?int $actorId,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $meta = [],
    ): void {
        $encodedMeta = json_encode($meta, JSON_THROW_ON_ERROR);
        Database::q(
            'INSERT INTO activity_log (workspace_id, actor_id, action, subject_type, subject_id, meta) VALUES (:workspace_id, :actor_id, :action, :subject_type, :subject_id, CAST(:meta AS jsonb))',
            [
                'workspace_id' => $workspaceId,
                'actor_id' => $actorId,
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'meta' => $encodedMeta,
            ],
        );
    }

    public static function listForWorkspace(int $workspaceId, int $limit = 30): array
    {
        return self::fetch($workspaceId, null, $limit);
    }

    public static function listSince(int $workspaceId, int $afterId, int $limit = 30): array
    {
        return self::fetch($workspaceId, $afterId, $limit);
    }

    private static function fetch(int $workspaceId, ?int $afterId, int $limit): array
    {
        $limit = max(1, min($limit, 100));
        $sql = <<<'SQL'
            SELECT a.id, a.action, a.subject_type, a.subject_id, a.meta,
                   a.created_at, a.actor_id, u.name AS actor_name
            FROM activity_log a
            LEFT JOIN users u ON u.id = a.actor_id
            WHERE a.workspace_id = :workspace_id
        SQL;
        $params = ['workspace_id' => $workspaceId];
        if ($afterId !== null) {
            $sql .= ' AND a.id > :after_id';
            $params['after_id'] = $afterId;
        }
        $sql .= ' ORDER BY a.id DESC LIMIT ' . $limit;

        $rows = Database::q($sql, $params)->fetchAll();
        foreach ($rows as &$row) {
            $decoded = json_decode((string) $row['meta'], true);
            $row['meta'] = is_array($decoded) ? $decoded : [];
        }

        return $rows;
    }
}
