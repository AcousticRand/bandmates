<?php

namespace Database\Factories;

use App\Enums\EnergyLevel;
use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Song>
 */
class SongFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $keys = ['C', 'C#', 'D', 'Eb', 'E', 'F', 'F#', 'G', 'Ab', 'A', 'Bb', 'B'];
        $genres = ['Rock', 'Pop', 'Country', 'Blues', 'Jazz', 'Folk', 'R&B', 'Alternative'];

        return [
            'title' => fake()->sentence(3),
            'artist' => fake()->name(),
            'album' => fake()->optional(0.6)->words(3, true),
            'genre' => fake()->randomElement($genres),
            'release_year' => fake()->optional()->year(),
            'lyrics' => fake()->optional()->paragraphs(3, true),
            'notes' => fake()->optional()->sentence(),
            'tempo' => fake()->optional()->numberBetween(60, 180).' BPM',
            'key' => fake()->optional()->randomElement($keys),
            'arrangement' => fake()->optional()->sentence(),
            'runtime' => fake()->boolean(60)
                ? sprintf('%d:%02d', fake()->numberBetween(1, 10), fake()->numberBetween(0, 59))
                : null,
            'is_acoustic' => fake()->boolean(30),
            'is_opener' => fake()->boolean(20),
            'energy_level' => fake()->optional(0.7)->randomElement(EnergyLevel::cases()),
            'is_active' => true,
        ];
    }
}
