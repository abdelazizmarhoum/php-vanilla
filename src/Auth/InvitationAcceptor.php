<?php
declare(strict_types=1);

namespace App\Auth;

use App\Core\Database;
use App\Models\Invitation;
use App\Models\Membership;
use App\Models\User;
use RuntimeException;

final class InvitationAcceptor
{
    private function __construct()
    {
    }

    public static function preview(string $rawToken): ?array
    {
        $invitation = Invitation::findByTokenHash(hash('sha256', $rawToken));
        if ($invitation === null || $invitation['accepted_at'] !== null || strtotime((string) $invitation['expires_at']) < time()) {
            return null;
        }

        return $invitation;
    }

    public static function accept(string $rawToken, int $userId): array
    {
        $invitation = self::preview($rawToken);
        if ($invitation === null) {
            throw new RuntimeException('This invitation link is invalid or has expired');
        }

        $user = User::findById($userId);
        if ($user === null || strcasecmp((string) $user['email'], (string) $invitation['email']) !== 0) {
            throw new RuntimeException('This invitation was for a different email address');
        }
        if (Membership::find((int) $invitation['workspace_id'], $userId) !== null) {
            throw new RuntimeException('You are already a member');
        }

        $pdo = Database::pdo();
        try {
            $pdo->beginTransaction();
            Membership::add((int) $invitation['workspace_id'], $userId, (string) $invitation['role']);
            Invitation::markAccepted((int) $invitation['id']);
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        return [
            'workspace' => [
                'id' => $invitation['workspace_id'],
                'name' => $invitation['workspace_name'],
                'slug' => $invitation['workspace_slug'],
            ],
            'role' => $invitation['role'],
        ];
    }
}
