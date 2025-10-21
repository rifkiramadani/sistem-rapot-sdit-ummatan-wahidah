<?php

namespace App\Traits;

use App\Enums\RoleEnum;
use App\Models\User;

trait PolicyTrait
{
    public function isSuperadmin(User $user): bool
    {
        return $user->role?->name === RoleEnum::SUPERADMIN->value;
    }

    public function isAdmin(User $user): bool
    {
        return $user->role?->name === RoleEnum::ADMIN->value;
    }

    public function isPrincipal(User $user): bool
    {
        return $user->role?->name === RoleEnum::PRINCIPAL->value;
    }

    public function isTeacher(User $user): bool
    {
        return $user->role?->name === RoleEnum::TEACHER->value;
    }

    /**
     * Helper for management roles (SUPERADMIN, ADMIN, PRINCIPAL).
     * These roles generally have full access within their scope.
     */
    public function isManagement(User $user): bool
    {
        return $this->isSuperadmin($user) || $this->isAdmin($user) || $this->isPrincipal($user);
    }
}
