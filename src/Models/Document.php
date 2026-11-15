<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Document
{
    private function __construct()
    {
    }

    public static function findById(int $id): ?array
    {
        $row = Database::q(
            'SELECT d.*, u.name AS author_name FROM documents d INNER JOIN users u ON u.id = d.author_id WHERE d.id = :id',
            ['id' => $id],
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public static function listForWorkspace(int $workspaceId): array
    {
        return Database::q(
            'SELECT id, title, author_id, updated_at, left(content, 200) AS excerpt FROM documents WHERE workspace_id = :workspace_id ORDER BY updated_at DESC, id DESC',
            ['workspace_id' => $workspaceId],
        )->fetchAll();
    }

    public static function create(int $workspaceId, int $authorId, string $title, string $content): array
    {
        $pdo = Database::pdo();
        try {
            $pdo->beginTransaction();
            $document = Database::q(
                'INSERT INTO documents (workspace_id, author_id, title, content) VALUES (:workspace_id, :author_id, :title, :content) RETURNING *',
                [
                    'workspace_id' => $workspaceId,
                    'author_id' => $authorId,
                    'title' => $title,
                    'content' => $content,
                ],
            )->fetch();
            if (!is_array($document)) {
                throw new \RuntimeException('Document creation returned no row.');
            }
            DocumentVersion::create((int) $document['id'], $authorId, $title, $content);
            $pdo->commit();

            return $document;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    public static function update(int $id, int $editorId, string $title, string $content): array
    {
        $pdo = Database::pdo();
        try {
            $pdo->beginTransaction();
            $document = Database::q(
                'UPDATE documents SET title = :title, content = :content, updated_at = NOW() WHERE id = :id RETURNING *',
                ['title' => $title, 'content' => $content, 'id' => $id],
            )->fetch();
            if (!is_array($document)) {
                throw new \RuntimeException('Document not found during update.');
            }
            $version = DocumentVersion::create($id, $editorId, $title, $content);
            $pdo->commit();
            $document['version_id'] = $version['id'];

            return $document;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    public static function delete(int $id): void
    {
        Database::q('DELETE FROM documents WHERE id = :id', ['id' => $id]);
    }

    public static function countForWorkspace(int $workspaceId): int
    {
        $row = Database::q(
            'SELECT COUNT(*) AS count FROM documents WHERE workspace_id = :workspace_id',
            ['workspace_id' => $workspaceId],
        )->fetch();

        return (int) ($row['count'] ?? 0);
    }
}