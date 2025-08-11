<?php

namespace Database\Seeders;

use App\Models\ClassSubject;
use App\Models\SchoolAcademicYear;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            SummativeSeeder::class,
            ClassSubjectSeeder::class,
            StudentClassroomSeeder::class,
            StudentSummativeSeeder::class,
        ]);
    }
}
