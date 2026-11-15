<?php
declare(strict_types=1);

namespace App\Auth;

use App\Models\Membership;

final class WorkspaceContext
{
    private static ?array $workspace = null;
    private static ?array $membership = null;

    private function __construct()
    {
    }

    public static function set(array $workspace, array $membership): void
    {
        self::$workspace = $workspace;
        self::$membership = $membership;
    }

    public static function workspace(): ?array
    {
        return self::$workspace;
    }

    public static function membership(): ?array
    {
        return self::$membership;
    }

    public static function role(): ?string
    {
        $role = self::$membership['role'] ?? null;

        return is_string($role) ? $role : null;
    }

    public static function can(string $capability): bool
    {
        $minimumRoles = [
            'read' => 'viewer',
            'write' => 'member',
            'manage' => 'admin',
            'delete' => 'owner',
        ];
        $minimumRole = $minimumRoles[$capability] ?? null;
        if ($minimumRole === null) {
            return false;
        }

        return Membership::rank(self::role() ?? '') >= Membership::rank($minimumRole);
    }
}