<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Building>
 */
class BuildingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(3, true);
        
        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'short_description' => fake()->sentence(),
            'year' => fake()->numberBetween(1950, 2025),
            'lat' => fake()->randomFloat(6, 47.3, 47.4), // Zurich coordinates
            'long' => fake()->randomFloat(6, 8.4, 8.6),
            'maps' => json_encode([
                'street' => fake()->streetAddress(),
                'city' => 'Zürich',
                'postal_code' => fake()->numberBetween(8000, 8099)
            ]),
        ];
    }

    /**
     * Indicate that the building is from a specific year.
     */
    public function year(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => $year,
        ]);
    }

    /**
     * Indicate that the building has a specific location.
     */
    public function location(float $lat, float $long): static
    {
        return $this->state(fn (array $attributes) => [
            'lat' => $lat,
            'long' => $long,
        ]);
    }
}