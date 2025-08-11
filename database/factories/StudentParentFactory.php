<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StudentParentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'father_name' => $this->faker->name('male'),
            'mother_name' => $this->faker->name('female'),
            'father_job' => $this->faker->jobTitle(),
            'mother_job' => $this->faker->jobTitle(),
            'address' => $this->faker->address(),
        ];
    }
}
