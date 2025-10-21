<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\SchoolAcademicYear;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, check if any teachers exist to be assigned to classrooms.
        if (Teacher::count() === 0) {
            $this->command->warn('No teachers found. Please run the TeacherSeeder before seeding classrooms.');
            return;
        }

        $schoolAcademicYears = SchoolAcademicYear::all();

        if ($schoolAcademicYears->isEmpty()) {
            $this->command->warn('No SchoolAcademicYear records found. Please run the SchoolAcademicYearSeeder first.');
            return;
        }

        // Distribute classrooms across academic years
        $totalClassrooms = 10;
        $classroomsPerYear = ceil($totalClassrooms / $schoolAcademicYears->count());

        $createdClassrooms = 0;

        foreach ($schoolAcademicYears as $index => $schoolAcademicYear) {
            // For the last academic year, adjust the count to reach exactly totalClassrooms
            $classroomsForThisYear = ($index === $schoolAcademicYears->count() - 1)
                ? ($totalClassrooms - $createdClassrooms)
                : $classroomsPerYear;

            Classroom::factory()->count($classroomsForThisYear)->create([
                'school_academic_year_id' => $schoolAcademicYear->id,
            ]);

            $createdClassrooms += $classroomsForThisYear;
            $this->command->info("Created {$classroomsForThisYear} classrooms for academic year: {$schoolAcademicYear->academicYear->name}");
        }

        $this->command->info("Created total of {$createdClassrooms} classrooms across all academic years.");
    }
}
