<?php

namespace Database\Factories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comment' => fake()->paragraph(),
            'published' => fake()->boolean(70), // 70% chance of being published
            'building_id' => Building::factory(),
        ];
    }

    /**
     * Indicate that the comment is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'published' => true,
        ]);
    }

    /**
     * Indicate that the comment is a draft (unpublished).
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'published' => false,
        ]);
    }

    /**
     * Indicate that the comment is for a specific building.
     */
    public function forBuilding(Building $building): static
    {
        return $this->state(fn (array $attributes) => [
            'building_id' => $building->id,
        ]);
    }
}