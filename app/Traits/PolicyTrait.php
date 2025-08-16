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

    /**
     * Anda bisa menambahkan fungsi-fungsi bantuan policy lainnya di sini di masa depan.
     * Contoh:
     *
     * protected function isAdmin(User $user): bool
     * {
     * return $user->role === RoleEnum::ADMIN;
     * }
     *
     * protected function isOwner(User $user, $model): bool
     * {
     * return $user->id === $model->user_id;
     * }
     */
}
