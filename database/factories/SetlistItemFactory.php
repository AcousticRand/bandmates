<?php

namespace Database\Factories;

use App\Models\SetlistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SetlistItem>
 */
class SetlistItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'set_number' => fake()->numberBetween(1, 3),
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
