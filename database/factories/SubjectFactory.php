<?php

namespace Database\Factories;

use App\Models\SchoolAcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subjectName = $this->faker->unique()->randomElement([
            'Mathematics',
            'Physics',
            'Chemistry',
            'Biology',
            'History',
            'Geography',
            'Sociology',
            'Economics',
            'English',
            'Indonesian',
            'Art',
            'Music',
            'Physical Education',
            'Computer Science',
            'Civics'
        ]);

        return [
            'name' => $subjectName,
            'description' => "A course on the fundamentals of {$subjectName}.",
            'school_academic_year_id' => SchoolAcademicYear::inRandomOrder()->first()->id,
        ];
    }
}
