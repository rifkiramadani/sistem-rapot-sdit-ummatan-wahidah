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
            $this->command->warn('No SchoolAcademicYear records found. Please run the SchoolSeeder first.');
            return;
        }

        // Create 15 subjects. The factory will handle assigning them.
        Subject::factory()->count(5)->create();

        $this->command->info('Created 5 subjects.');
    }
}
