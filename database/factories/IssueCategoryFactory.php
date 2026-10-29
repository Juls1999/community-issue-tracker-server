<?php

namespace Database\Factories;

use App\Models\IssueCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IssueCategory>
 */
class IssueCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
