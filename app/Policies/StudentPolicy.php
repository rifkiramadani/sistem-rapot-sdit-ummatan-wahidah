<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Traits\PolicyTrait;

class StudentPolicy
{
    use PolicyTrait;

    // Only management can manage student master data

    public function viewAny(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function view(User $user, Student $student): bool
    {
        return $this->isManagement($user);
    }

    public function create(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function update(User $user, Student $student): bool
    {
        return $this->isManagement($user);
    }

    public function delete(User $user, Student $student): bool
    {
        return $this->isManagement($user);
    }

    public function bulkDelete(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function restore(User $user, Student $student): bool
    {
        return $this->isManagement($user);
    }

    public function forceDelete(User $user, Student $student): bool
    {
        return $this->isManagement($user);
    }
}