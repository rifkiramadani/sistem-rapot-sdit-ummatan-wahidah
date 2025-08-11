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
        AcademicYear::factory()->count(5)->create();

        School::factory()->count(2)->create();
    }
}
