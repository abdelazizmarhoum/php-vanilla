<?php
declare(strict_types=1);

namespace App\Auth;

use App\Models\Invitation;
use InvalidArgumentException;

final class InvitationService
{
    private function __construct()
    {
    }

    public static function invite(int $workspaceId, string $email, string $role, int $invitedBy): array
    {
        $email = trim($email);
        $role = trim($role);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Enter a valid email address');
        }
        if (!in_array($role, ['admin', 'member', 'viewer'], true)) {
            throw new InvalidArgumentException('Invalid invitation role');
        }

        $existing = Invitation::findPendingByWorkspaceAndEmail($workspaceId, $email);
        if ($existing !== null) {
            Invitation::delete((int) $existing['id']);
        }

        $rawToken = bin2hex(random_bytes(32));
        $expiresAt = date('c', time() + (7 * 86400));
        $row = Invitation::create(
            $workspaceId,
            $email,
            $role,
            hash('sha256', $rawToken),
            $invitedBy,
            $expiresAt,
        );
        $inviteUrl = url('/invitations/' . $rawToken);
        $line = sprintf(
            "[INVITE] %s workspace=%d role=%s token=%s expires=%s url=%s%s",
            $email,
            $workspaceId,
            $role,
            $rawToken,
            $expiresAt,
            $inviteUrl,
            PHP_EOL,
        );
        file_put_contents(dirname(__DIR__, 2) . '/storage/logs/mail.log', $line, FILE_APPEND | LOCK_EX);

        return [
            'invitation' => $row,
            'raw_token' => $rawToken,
            'invite_url' => $inviteUrl,
        ];
    }
}
