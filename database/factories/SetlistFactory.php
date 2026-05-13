<?php

namespace Database\Factories;

use App\Models\Setlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setlist>
 */
class SetlistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'           => fake()->words(3, true),
            'description'    => fake()->optional()->sentence(),
            'number_of_sets' => fake()->numberBetween(1, 3),
        ];
    }
}
