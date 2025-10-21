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

        $schoolAcademicYears = SchoolAcademicYear::all();

        if ($schoolAcademicYears->isEmpty()) {
            $this->command->warn('No SchoolAcademicYear records found. Please run the SchoolAcademicYearSeeder first.');
            return;
        }

        // Distribute teachers across academic years
        $totalTeachers = 20;
        $teachersPerYear = ceil($totalTeachers / $schoolAcademicYears->count());

        $createdTeachers = 0;

        foreach ($schoolAcademicYears as $index => $schoolAcademicYear) {
            // For the last academic year, adjust the count to reach exactly totalTeachers
            $teachersForThisYear = ($index === $schoolAcademicYears->count() - 1)
                ? ($totalTeachers - $createdTeachers)
                : $teachersPerYear;

            Teacher::factory()->count($teachersForThisYear)->create([
                'school_academic_year_id' => $schoolAcademicYear->id,
            ]);

            $createdTeachers += $teachersForThisYear;
            $this->command->info("Created {$teachersForThisYear} teachers for academic year: {$schoolAcademicYear->academicYear->name}");
        }

        $this->command->info("Created total of {$createdTeachers} teachers with user accounts across all academic years.");
    }
}
