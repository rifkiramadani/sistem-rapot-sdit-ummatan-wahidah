<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\User;
use App\Traits\PolicyTrait;

class ClassroomSubjectPolicy
{
    use PolicyTrait;

    /**
     * Who can view the list of classroom subjects?
     * Management and Teachers can view the list page, but teachers will see filtered data.
     */
    public function viewAny(User $user): bool
    {
        return $this->isManagement($user) || $this->isTeacher($user);
    }

    /**
     * Who can view the details of a classroom subject?
     */
    public function view(User $user, ClassroomSubject $classroomSubject): bool
    {
        // 1. Management (SUPERADMIN, ADMIN, PRINCIPAL) can view all classroom subjects.
        if ($this->isManagement($user)) {
            return true;
        }

        // 2. A teacher can only view it if they are the homeroom teacher of the classroom.
        if ($this->isTeacher($user)) {
            // Get the teacher data for this user IN THIS classroom's ACADEMIC YEAR.
            $teacherRecord = $user->teacher()
                ->where('school_academic_year_id', $classroomSubject->classroom->school_academic_year_id)
                ->first();

            // If this user is not a teacher in this academic year, deny.
            if (!$teacherRecord) {
                return false;
            }

            // Allow ONLY if the logged-in teacher's ID = the homeroom teacher's ID
            return $teacherRecord->id === $classroomSubject->classroom->teacher_id;
        }

        return false;
    }

    /**
     * Who can create classroom subjects? Only Management.
     */
    public function create(User $user): bool
    {
        // 1. Management (SUPERADMIN, ADMIN, PRINCIPAL) can view all classroom subjects.
        if ($this->isManagement($user)) {
            return true;
        }

        // 2. A teacher can only view it if they are the homeroom teacher of the classroom.
        if ($this->isTeacher($user)) {
            // Get the teacher data for this user IN THIS classroom's ACADEMIC YEAR.
            $teacherRecord = $user->teacher()
                ->where('school_academic_year_id', $classroomSubject->classroom->school_academic_year_id)
                ->first();

            // If this user is not a teacher in this academic year, deny.
            if (!$teacherRecord) {
                return false;
            }

            // Allow ONLY if the logged-in teacher's ID = the homeroom teacher's ID
            return $teacherRecord->id === $classroomSubject->classroom->teacher_id;
        }

        return false;
    }

    /**
     * Who can update classroom subjects?
     * Management is allowed, or the Teacher who manages that classroom.
     */
    public function update(User $user, ClassroomSubject $classroomSubject): bool
    {
        if ($this->isManagement($user)) {
            return true;
        }

        if ($this->isTeacher($user)) {
            $teacherRecord = $user->teacher()
                ->where('school_academic_year_id', $classroomSubject->classroom->school_academic_year_id)
                ->first();

            if (!$teacherRecord) return false;

            return $teacherRecord->id === $classroomSubject->classroom->teacher_id;
        }

        return false;
    }

    /**
     * Who can delete classroom subjects? Only Management.
     */
    public function delete(User $user, ClassroomSubject $classroomSubject): bool
    {
        if ($this->isManagement($user)) {
            return true;
        }

        if ($this->isTeacher($user)) {
            $teacherRecord = $user->teacher()
                ->where('school_academic_year_id', $classroomSubject->classroom->school_academic_year_id)
                ->first();

            if (!$teacherRecord) return false;

            return $teacherRecord->id === $classroomSubject->classroom->teacher_id;
        }

        return false;
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