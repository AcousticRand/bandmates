<?php

namespace Database\Factories;

use App\Models\SongAnnotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SongAnnotation>
 */
class SongAnnotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'annotation' => fake()->paragraph(),
        ];
    }
}
