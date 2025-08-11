<?php

namespace Database\Seeders;

use App\Models\SchoolAcademicYear;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have school-year links before creating teachers
        if (SchoolAcademicYear::count() === 0) {
            $this->command->warn('No SchoolAcademicYear records found. Please run the SchoolAcademicYearSeeder first.');
            return;
        }

        // Create 20 teachers. This will also create 20 new user accounts.
        Teacher::factory()->count(20)->create();

        $this->command->info('Created 20 teachers with user accounts.');
    }
}
