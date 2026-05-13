<?php

namespace Database\Factories;

use App\Enums\GigStatus;
use App\Models\Gig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gig>
 */
class GigFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'   => fake()->words(4, true),
            'date'   => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'notes'  => fake()->optional()->sentence(),
            'status' => fake()->randomElement(GigStatus::cases()),
        ];
    }
}
