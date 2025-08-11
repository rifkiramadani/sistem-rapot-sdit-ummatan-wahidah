<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 1. Get a random teacher. This is the key to ensuring data integrity.
        $teacher = Teacher::inRandomOrder()->first();

        return [
            // 2. Generate a classroom name, e.g., "Class 1A"
            'name' => 'Class ' . $this->faker->randomElement(['1A', '1B', '2A', '2B', '3A', '3B', '4A', '5B', '6C']),

            // 3. Use the ID from the teacher we found.
            'teacher_id' => $teacher->id,

            // 4. Use the school_academic_year_id from the same teacher.
            'school_academic_year_id' => $teacher->school_academic_year_id,
        ];
    }
}
