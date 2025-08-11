<?php

namespace Database\Seeders;

use App\Models\Classroom;
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

        // Create 10 classrooms. The factory will handle the logic.
        Classroom::factory()->count(10)->create();

        $this->command->info('Created 10 classrooms.');
    }
}
