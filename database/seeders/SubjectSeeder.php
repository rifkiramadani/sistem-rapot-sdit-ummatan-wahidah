<?php

namespace Database\Seeders;

use App\Models\SchoolAcademicYear;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, check if any school academic years exist.
        if (SchoolAcademicYear::count() === 0) {
            $this->command->warn('No SchoolAcademicYear records found. Please run the SchoolAcademicYearSeeder first.');
            return;
        }

        $schoolAcademicYears = SchoolAcademicYear::all();

        if ($schoolAcademicYears->isEmpty()) {
            $this->command->warn('No SchoolAcademicYear records found. Please run the SchoolAcademicYearSeeder first.');
            return;
        }

        // Distribute subjects across academic years
        $totalSubjects = 15;
        $subjectsPerYear = ceil($totalSubjects / $schoolAcademicYears->count());

        $createdSubjects = 0;

        foreach ($schoolAcademicYears as $index => $schoolAcademicYear) {
            // For the last academic year, adjust the count to reach exactly totalSubjects
            $subjectsForThisYear = ($index === $schoolAcademicYears->count() - 1)
                ? ($totalSubjects - $createdSubjects)
                : $subjectsPerYear;

            Subject::factory()->count($subjectsForThisYear)->create([
                'school_academic_year_id' => $schoolAcademicYear->id,
            ]);

            $createdSubjects += $subjectsForThisYear;
            $this->command->info("Created {$subjectsForThisYear} subjects for academic year: {$schoolAcademicYear->academicYear->name}");
        }

        $this->command->info("Created total of {$createdSubjects} subjects across all academic years.");
    }
}
