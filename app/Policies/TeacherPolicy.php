<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;
use App\Traits\PolicyTrait;

class TeacherPolicy
{
    use PolicyTrait;

    // Only management can manage teacher master data

    public function viewAny(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function view(User $user, Teacher $teacher): bool
    {
        return $this->isManagement($user);
    }

    public function create(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function update(User $user, Teacher $teacher): bool
    {
        return $this->isManagement($user);
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $this->isManagement($user);
    }

    public function bulkDelete(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function restore(User $user, Teacher $teacher): bool
    {
        return $this->isManagement($user);
    }

    public function forceDelete(User $user, Teacher $teacher): bool
    {
        return $this->isManagement($user);
    }
}