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
        $this->command->info('🌱 Starting database seeding for centralized school system...');

        // Step 1: Create roles and super admin user first
        $this->call([
            RoleSeeder::class,
        ]);

        $this->command->info('✅ Roles and permissions created');

        // Create super admin user
        $superAdminRole = Role::where('name', RoleEnum::SUPERADMIN->value)->first();

        if ($superAdminRole) {
            User::factory()->create([
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'), // Default password is 'password'
                'role_id' => $superAdminRole->id,
            ]);
            $this->command->info('✅ Super admin user created (superadmin@example.com / password)');
        }

        // PERBAIKAN: Tambahkan user admin
        $adminRole = Role::where('name', RoleEnum::ADMIN->value)->first();
        if ($adminRole) {
            User::factory()->create([
                'name' => 'Admin Sekolah',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
            ]);
            $this->command->info('✅ Admin user created (admin@example.com / password)');
        }

        // Step 2: Create school structure and academic years
        $this->call([
            SchoolAcademicYearSeeder::class,
        ]);

        $this->command->info('✅ Centralized school and academic years created');

        // Step 3: Create teachers and students (these depend on school-academic-year relationships)
        $this->call([
            TeacherSeeder::class,
            StudentSeeder::class,
        ]);

        $this->command->info('✅ Teachers and students created');

        // Step 4: Create classrooms and subjects (depend on teachers and students)
        $this->call([
            ClassroomSeeder::class,
            SubjectSeeder::class,
        ]);

        $this->command->info('✅ Classrooms and subjects created');

        // Step 5: Create summative types for assessment framework
        $this->call([
            SummativeTypeSeeder::class,
        ]);

        $this->command->info('✅ Summative assessment types created');

        // Step 6: Create relationships between classrooms and subjects
        $this->call([
            ClassroomSubjectSeeder::class,
        ]);

        $this->command->info('✅ Classroom-subject relationships created');

        // Step 7: Create student-classroom assignments
        $this->call([
            ClassroomStudentSeeder::class,
        ]);

        $this->command->info('✅ Student-classroom assignments created');

        // Step 8: Create summative assessments and student grades
        $this->call([
            SummativeSeeder::class,
            StudentSummativeSeeder::class,
        ]);

        $this->command->info('✅ Summative assessments and student grades created');

        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('   - 1 Centralized School (SDIT Ummatan Wahidah)');
        $this->command->info('   - 5 Academic Years (2021/2022 - 2025/2026)');
        $this->command->info('   - 20 Teachers with user accounts');
        $this->command->info('   - 50 Students with parent/guardian data');
        $this->command->info('   - 10 Classrooms distributed across years');
        $this->command->info('   - 15 Subjects distributed across years');
        $this->command->info('   - Summative assessments and student grades');
        $this->command->info('');
        $this->command->info('🔑 Login Credentials:');
        $this->command->info('   Super Admin: superadmin@example.com / password');
        $this->command->info('   Admin: admin@example.com / password');  // PERBAIKAN: Ditambahkan
        $this->command->info('   Principal: kepsek@ummatanwahidah.sch.id / password');
    }
}
