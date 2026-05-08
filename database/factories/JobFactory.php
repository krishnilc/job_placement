<?php

namespace Database\Factories;

use App\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->name,
            'user_id' => 1, // Assuming you have 3 users
            'job_type_id' => rand(1, 5), // Assuming you have 3 job types
            'category_id' => rand(1, 5), // Assuming you have 5 categories
            'vacancy' => rand(1, 5),
            'location' => fake()->city(),
            'description' => fake()->text(),
            'experience' => rand(0, 10),
            'company_name' => fake()->company(),

        ];
    }
}
