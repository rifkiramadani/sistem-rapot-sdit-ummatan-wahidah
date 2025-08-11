<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\School>
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Use company name for a realistic-sounding school name
            'name' => $this->faker->unique()->company(),

            // Generate a random 8-digit NPSN number as a string
            'npsn' => $this->faker->numerify('########'),

            'address' => $this->faker->address(),
            'postal_code' => $this->faker->postcode(),
            'website' => $this->faker->unique()->domainName(),
            'email' => $this->faker->unique()->safeEmail(),

            'school_principal_id' => User::factory()->asAdmin(),

            'current_academic_year' => '2025/2026',
            'place_date_raport' => $this->faker->city(),
            'place_date_sts' => $this->faker->city(),
        ];
    }
}
