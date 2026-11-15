<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class DocumentVersion
{
    private function __construct()
    {
    }

    public static function listForDocument(int $documentId, int $limit = 50): array
    {
        $limit = max(1, min($limit, 500));

        return Database::q(
            'SELECT v.*, u.name AS editor_name FROM document_versions v INNER JOIN users u ON u.id = v.editor_id WHERE v.document_id = :document_id ORDER BY v.created_at DESC, v.id DESC LIMIT CAST(:limit AS INTEGER)',
            ['document_id' => $documentId, 'limit' => $limit],
        )->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $row = Database::q(
            'SELECT v.*, u.name AS editor_name FROM document_versions v INNER JOIN users u ON u.id = v.editor_id WHERE v.id = :id',
            ['id' => $id],
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public static function create(int $documentId, int $editorId, string $title, string $content): array
    {
        $row = Database::q(
            'INSERT INTO document_versions (document_id, editor_id, title, content) VALUES (:document_id, :editor_id, :title, :content) RETURNING *',
            [
                'document_id' => $documentId,
                'editor_id' => $editorId,
                'title' => $title,
                'content' => $content,
            ],
        )->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Document version creation returned no row.');
        }

        return $row;
    }
}