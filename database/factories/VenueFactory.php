<?php

namespace Database\Factories;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'     => fake()->company() . ' ' . fake()->randomElement(['Theater', 'Club', 'Hall', 'Arena', 'Bar']),
            'address'  => fake()->optional()->streetAddress(),
            'city'     => fake()->city(),
            'state'    => fake()->stateAbbr(),
            'capacity' => fake()->optional()->numberBetween(50, 50000),
            'notes'    => fake()->optional()->sentence(),
        ];
    }
}
