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

        $schoolAcademicYears = SchoolAcademicYear::all();

        if ($schoolAcademicYears->isEmpty()) {
            $this->command->warn('No SchoolAcademicYear records found. Please run the SchoolAcademicYearSeeder first.');
            return;
        }

        // Distribute students across academic years
        $totalStudents = 50;
        $studentsPerYear = ceil($totalStudents / $schoolAcademicYears->count());

        $createdStudents = 0;

        foreach ($schoolAcademicYears as $index => $schoolAcademicYear) {
            // For the last academic year, adjust the count to reach exactly totalStudents
            $studentsForThisYear = ($index === $schoolAcademicYears->count() - 1)
                ? ($totalStudents - $createdStudents)
                : $studentsPerYear;

            Student::factory()->count($studentsForThisYear)->create([
                'school_academic_year_id' => $schoolAcademicYear->id,
            ]);

            $createdStudents += $studentsForThisYear;
            $this->command->info("Created {$studentsForThisYear} students for academic year: {$schoolAcademicYear->academicYear->name}");
        }

        $this->command->info("Created total of {$createdStudents} students with parent and guardian data across all academic years.");
    }
}