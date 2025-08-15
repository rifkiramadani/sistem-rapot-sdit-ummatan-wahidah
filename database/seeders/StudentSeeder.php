<?php

namespace Database\Seeders;

use App\Models\SchoolAcademicYear;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have school-year links before creating students
        if (SchoolAcademicYear::count() === 0) {
            $this->command->warn('No SchoolAcademicYear records found. Please run the SchoolAcademicYearSeeder first.');
            return;
        }

        // Create 50 students, each with a parent and guardian record.
        Student::factory()->count(50)->create();

        $this->command->info('Created 50 students with parent and guardian data.');
    }
}