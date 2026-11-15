<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Comment
{
    private function __construct()
    {
    }

    public static function listForDocument(int $documentId): array
    {
        return Database::q(
            <<<'SQL'
            SELECT c.id, c.body, c.created_at, c.author_id, u.name AS author_name, c.parent_id
            FROM comments c
            INNER JOIN users u ON u.id = c.author_id
            WHERE c.document_id = :document_id
            ORDER BY COALESCE(c.parent_id, c.id), c.created_at ASC, c.id ASC
            SQL,
            ['document_id' => $documentId],
        )->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $row = Database::q(
            'SELECT c.id, c.document_id, c.body, c.created_at, c.author_id, u.name AS author_name, c.parent_id FROM comments c INNER JOIN users u ON u.id = c.author_id WHERE c.id = :id',
            ['id' => $id],
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public static function create(int $documentId, int $authorId, ?int $parentId, string $body): array
    {
        $row = Database::q(
            'INSERT INTO comments (document_id, author_id, parent_id, body) VALUES (:document_id, :author_id, :parent_id, :body) RETURNING *',
            [
                'document_id' => $documentId,
                'author_id' => $authorId,
                'parent_id' => $parentId,
                'body' => $body,
            ],
        )->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Comment creation returned no row.');
        }

        return self::findById((int) $row['id']) ?? $row;
    }

    public static function delete(int $id, int $authorId): bool
    {
        $statement = Database::q(
            'DELETE FROM comments WHERE id = :id AND author_id = :author_id',
            ['id' => $id, 'author_id' => $authorId],
        );

        return $statement->rowCount() === 1;
    }

    public static function deleteByModerator(int $id): bool
    {
        $statement = Database::q('DELETE FROM comments WHERE id = :id', ['id' => $id]);

        return $statement->rowCount() === 1;
    }

    public static function countForDocument(int $documentId): int
    {
        $row = Database::q(
            'SELECT COUNT(*) AS count FROM comments WHERE document_id = :document_id',
            ['document_id' => $documentId],
        )->fetch();

        return (int) ($row['count'] ?? 0);
    }
}