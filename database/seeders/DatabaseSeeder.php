<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SchoolAcademicYearSeeder::class,
            StudentSeeder::class,
            TeacherSeeder::class,
            ClassroomSeeder::class,
            SubjectSeeder::class,
            SummativeTypeSeeder::class,
            ClassroomSubjectSeeder::class,
            ClassroomStudentSeeder::class,
            SummativeSeeder::class,
            StudentSummativeSeeder::class,
        ]);

        $superAdminRole = Role::where('name', RoleEnum::SUPERADMIN->value)->first();

        // 2. If the role exists, create the user.
        if ($superAdminRole) {
            User::factory()->create([
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'), // Default password is 'password'
                'role_id' => $superAdminRole->id,
            ]);
        }
    }
}