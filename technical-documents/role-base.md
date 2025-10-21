# Role-Based Access Control (RBAC) & Policies

Document purpose, core concepts, role matrix, user flow, and concrete policy implementation examples for the application's RBAC system using Laravel Policies.

## Table of contents
- [1. Document Purpose](#1-document-purpose)  
- [2. Core Concepts](#2-core-concepts)  
  - [2.1 User Role Structure](#21-user-role-structure)  
  - [2.2 Route Structure](#22-route-structure)  
- [3. Role & Access Rights Matrix](#3-role--access-rights-matrix)  
  - [Key Restriction Rules for TEACHER](#key-restriction-rules-for-teacher)  
- [4. User Flow & Authorization](#4-user-flow--authorization)  
- [5. Policy Implementation Scheme](#5-policy-implementation-scheme)  
  - [5.1 Update PolicyTrait.php](#51-update-policytraitphp)  
  - [5.2 Calling Authorization in Controllers](#52-calling-authorization-in-controllers)  
  - [5.3 Policy Schema Examples](#53-policy-schema-examples)  
    - [A. AcademicYearPolicy.php (Root Route Example)](#a-academicyearpolicyphp-root-route-example)  
    - [B. TeacherPolicy.php (School-Based Master Data Example)](#b-teacherpolicyphp-school-based-master-data-example)  
    - [C. ClassroomPolicy.php (School-Based Restricted Data Example)](#c-classroompolicyphp-school-based-restricted-data-example)  
    - [D. SchoolAcademicYearPolicy.php (Policy for the Dashboard)](#d-schoolacademicyearpolicyphp-policy-for-the-dashboard)

---

## 1. Document Purpose

This document explains the access control architecture (RBAC) within the application, using Laravel Policies as the primary authorization mechanism. Its purpose is to ensure that each user role has appropriate and limited access rights according to their responsibilities.

---

## 2. Core Concepts

This system has two main concepts that determine access rights: Roles and Route Types.

### 2.1 User Role Structure

Access rights are determined by the `App\Enums\RoleEnum` enum, which is bound to the `User` model.

- SUPERADMIN: Full system administrator. Has access to all features without restrictions.  
- ADMIN: Foundation or multi-school administrator. Has full administrative access, similar to SUPERADMIN for school operations.  
- PRINCIPAL: A manager specific to a single `SchoolAcademicYear`. Has full access to all data within the selected School Academic Year.  
- TEACHER: A highly restricted operational user. Their access is specific per `SchoolAcademicYear` and limited only to the classroom data they manage.

### 2.2 Route Structure

The application has two types of protected route architectures:

- Root Routes (Global Routes)  
  - Prefix: `protected/`  
  - Example: `protected/academic-years`, `protected/schools`  
  - Purpose: Manage master data or global data that is not tied to a specific school academic year (e.g., creating new academic years, managing school data).

- School-Based Routes (School Operational Routes)  
  - Prefix: `protected/{schoolAcademicYear}/`  
  - Example: `protected/{schoolAcademicYear}/teachers`, `protected/{schoolAcademicYear}/classrooms`  
  - Purpose: Manage daily operational data specific to the currently active school academic year.

---

## 3. Role & Access Rights Matrix

The following table summarizes the access rights for each role:

| Role       | Root Routes | School-Based Routes (Master Data) | School-Based Routes (Class Data) | Data Scope |
|------------|-------------|-----------------------------------|----------------------------------|------------|
| SUPERADMIN | ✅ Full Access | ✅ Full Access | ✅ Full Access | Global (All data) |
| ADMIN      | ✅ Full Access | ✅ Full Access | ✅ Full Access | Global (All data) |
| PRINCIPAL  | ✅ Full Access | ✅ Full Access | ✅ Full Access | All data within `{schoolAcademicYear}` |
| TEACHER    | ❌ Forbidden   | ❌ Forbidden   | ✅ Very Limited | Only Classroom data related to them |

### Key Restriction Rules for TEACHER:
- CANNOT access Root Routes (e.g., `protected/academic-years`).  
- CANNOT access Master Data routes within the school (e.g., `.../teachers`, `.../students`, `.../subjects`).  
- Can ONLY access routes related to `.../classrooms/...`.  
- Access to `.../classrooms/{classroom}` is ONLY permitted if the Teacher is the homeroom teacher (`teacher_id`) for that `Classroom`.  
- The Dashboard (`protected/{schoolAcademicYear}`) must display data filtered only for the classes they manage.

---

## 4. User Flow & Authorization

1. Login: User logs in using their User credentials.  
2. Academic Year Selection:
   - SUPERADMIN and ADMIN can directly access Root Routes or select a `SchoolAcademicYear` to enter School-Based Routes.
   - PRINCIPAL and TEACHER must select a `SchoolAcademicYear` after login to proceed to the operational dashboard.
3. Academic Year Access Validation:
   - When a TEACHER selects a `SchoolAcademicYear`, the system must validate that the Teacher is registered in that academic year (via `Teacher->school_academic_year_id`).
4. Per-Route Authorization:
   - Every request to a controller should call `Gate::authorize()` at the beginning of the method.
   - Laravel will automatically resolve the corresponding policy based on the bound model or method name.
   - The policy will check the user's role (`User`) and data ownership (if required).

---

## 5. Policy Implementation Scheme

To achieve this architecture, update the `PolicyTrait` and create policies for each model.

### 5.1 Update PolicyTrait.php

Add helpers for all roles to make the policies easy to read.

```php
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
```

---

### 5.2 Calling Authorization in Controllers

All controller methods must begin with `Gate::authorize()`.

Example: `TeacherController@index` (Accessing Teacher Master Data)

```php
<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Models\SchoolAcademicYear;
use App\Models\Teacher; // Important
use Illuminate\Support\Facades\Gate; // Important

class TeacherController extends Controller
{
    public function index(SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can view the list of teachers?
        // Laravel will automatically look for 'viewAny' in 'TeacherPolicy'
        Gate::authorize('viewAny', Teacher::class); 

        // ... remaining logic ...
    }

    public function create(SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can create a new teacher?
        Gate::authorize('create', Teacher::class);

        // ... remaining logic ...
    }
}
```

Example: `ClassroomController@show` (Accessing Specific Class Data)

```php
<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Models\SchoolAcademicYear;
use App\Models\Classroom; // Important
use Illuminate\Support\Facades\Gate; // Important

class ClassroomController extends Controller
{
    public function show(SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        // Authorization: Who can view the details of this class?
        // Laravel will look for 'view' in 'ClassroomPolicy'
        Gate::authorize('view', $classroom); 

        // ... remaining logic ...
    }
}
```

---

### 5.3 Policy Schema Examples

Below are sample policies implementing the defined access rules.

#### A. AcademicYearPolicy.php (Root Route Example)

This policy must allow management roles (SUPERADMIN, ADMIN, PRINCIPAL) but block TEACHER.

```php
<?php

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;
use App\Traits\PolicyTrait;
use Illuminate\Auth\Access\Response;

class AcademicYearPolicy
{
    use PolicyTrait;

    // Management roles (SUPERADMIN, ADMIN, PRINCIPAL) can manage academic years

    public function viewAny(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function view(User $user, AcademicYear $academicYear): bool
    {
        return $this->isManagement($user);
    }

    public function create(User $user): bool
    {
        return $this->isManagement($user);
    }

    public function update(User $user, AcademicYear $academicYear): bool
    {
        return $this->isManagement($user);
    }

    public function delete(User $user, AcademicYear $academicYear): bool
    {
        return $this->isManagement($user);
    }

    // ... (other methods)
}
```

#### B. TeacherPolicy.php (School-Based Master Data Example)

This policy has the same logic as `AcademicYearPolicy`: allow management, block teachers. The policies for `Student` and `Subject` will be identical.

```php
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
}
```

#### C. ClassroomPolicy.php (School-Based Restricted Data Example)

This is the most complex policy, which differentiates access for TEACHER.

```php
<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\SchoolAcademicYear;
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
            // Assumption: `teachers` relationship exists on User model: hasMany(Teacher::class, 'user_id')
            $teacherRecord = $user->teachers()
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
            $teacherRecord = $user->teachers()
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
    public function delete(User $user, Classroom $classroom): bool
    {
        return $this->isManagement($user);
    }
}
```

#### D. SchoolAcademicYearPolicy.php (Policy for the Dashboard)

This policy validates whether a user can access a specific academic year's dashboard.

```php
<?php

namespace App\Policies;

use App\Models\SchoolAcademicYear;
use App\Models\User;
use App\Traits\PolicyTrait;

class SchoolAcademicYearPolicy
{
    use PolicyTrait;

    /**
     * Who can view/access this academic year's dashboard?
     */
    public function view(User $user, SchoolAcademicYear $schoolAcademicYear): bool
    {
        // 1. Management can view all.
        if ($this->isManagement($user)) {
            return true;
        }

        // 2. A teacher is allowed, AS LONG AS they are registered in that academic year.
        if ($this->isTeacher($user)) {
            // Assumption: `teachers` relationship exists on User model: hasMany(Teacher::class, 'user_id')
            return $user->teachers()->where('school_academic_year_id', $schoolAcademicYear->id)->exists();
        }
        
        return false;
    }

    // Other methods (create, update, delete) for managing SchoolAcademicYear
    // Likely only for Superadmin/Admin
    public function create(User $user): bool
    {
        return $this->isSuperadmin($user) || $this->isAdmin($user);
    }
    
    // ...
}
```

---

If you'd like, I can:
- Export this to a repository README or a specific file path.  
- Generate unit tests for the policies.  
- Provide sample controller implementations that filter classroom lists for teachers.  
Tell me which of the above you'd like next.