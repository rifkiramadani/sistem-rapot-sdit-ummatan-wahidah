<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;
use App\Traits\PolicyTrait;

class ClassroomPolicy
{
    use PolicyTrait;

    /**
     * Who can view the Class List Page?
     * Management and Teachers can view the list page, but teachers will see filtered data.
     */
    public function viewAny(User $user): bool
    {
        return $this->isManagement($user) || $this->isTeacher($user);
    }

    /**
     * Who can view the *details* of a class?
     */
    public function view(User $user, Classroom $classroom): bool
    {
        // 1. Management (SUPERADMIN, ADMIN, PRINCIPAL) can view all classes.
        if ($this->isManagement($user)) {
            return true;
        }

        // 2. A teacher can only view it if they are the homeroom teacher.
        if ($this->isTeacher($user)) {
            // Get the teacher data for this user IN THIS class's ACADEMIC YEAR.
            // Assumption: `teacher` relationship exists on User model: hasMany(Teacher::class, 'user_id')
            $teacherRecord = $user->teacher()
                                 ->where('school_academic_year_id', $classroom->school_academic_year_id)
                                 ->first();

            // If this user is not a teacher in this academic year, deny.
            if (!$teacherRecord) {
                return false;
            }

            // Allow ONLY if the logged-in teacher's ID = the homeroom teacher's ID
            return $teacherRecord->id === $classroom->teacher_id;
        }

        return false;
    }

    /**
     * Who can create a class? Only Management.
     */
    public function create(User $user): bool
    {
        return $this->isManagement($user);
    }

    /**
     * Who can update a class?
     * Management is allowed, or the Teacher who manages that class.
     */
    public function update(User $user, Classroom $classroom): bool
    {
        if ($this->isManagement($user)) {
            return true;
        }

        if ($this->isTeacher($user)) {
            $teacherRecord = $user->teacher()
                                 ->where('school_academic_year_id', $classroom->school_academic_year_id)
                                 ->first();

            if (!$teacherRecord) return false;

            return $teacherRecord->id === $classroom->teacher_id;
        }

        return false;
    }

    /**
     * Who can delete a class? Only Management.
     */
    public function delete(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function bulkDelete(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function restore(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function forceDelete(User $user): bool
    {
        return $this->isManagement($user);
    }
}