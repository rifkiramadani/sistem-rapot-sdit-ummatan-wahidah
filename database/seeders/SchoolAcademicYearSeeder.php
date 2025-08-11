<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolAcademicYear;
use Illuminate\Database\Seeder;

class SchoolAcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        School::factory()->count(2)->create();

        AcademicYear::factory()->count(5)->create();
        // Get all schools and academic years from the database
        $schools = School::all();
        $academicYears = AcademicYear::all();

        // Check if we have schools and years to link
        if ($schools->isEmpty() || $academicYears->isEmpty()) {
            $this->command->warn('No schools or academic years found. Please seed them first.');
            return;
        }

        // Loop through each school
        foreach ($schools as $school) {
            // Loop through each academic year
            foreach ($academicYears as $academicYear) {
                // Use firstOrCreate to link them, avoiding duplicates
                SchoolAcademicYear::firstOrCreate([
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                ]);
            }
        }

        $this->command->info('Linked all schools with all academic years.');
    }
}
