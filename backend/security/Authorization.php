<?php
declare(strict_types=1);

namespace FixIT\Security;

use RuntimeException;

final class Authorization
{
    /** @param array<string, mixed>|null $user */
    public static function allows(?array $user, array $roles): bool
    {
        return $user !== null && in_array($user['perfil'] ?? null, $roles, true);
    }

    /** @param array<string, mixed>|null $user */
    public static function requireUser(?array $user): array
    {
        if ($user === null) {
            throw new RuntimeException('AUTH_REQUIRED', 401);
        }
        return $user;
    }

    /** @param array<string, mixed>|null $user */
    public static function requireRoles(?array $user, array $roles): array
    {
        self::requireUser($user);
        if (!self::allows($user, $roles)) {
            throw new RuntimeException('FORBIDDEN', 403);
        }
        return $user;
    }
}
