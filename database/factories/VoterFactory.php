<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voter>
 */
class VoterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ip_address' => fake()->ipv4(),
            'hash' => Str::random(32),
        ];
    }

    /**
     * Indicate that the voter has a specific IP address.
     */
    public function withIp(string $ipAddress): static
    {
        return $this->state(fn (array $attributes) => [
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Indicate that the voter has a specific hash.
     */
    public function withHash(string $hash): static
    {
        return $this->state(fn (array $attributes) => [
            'hash' => $hash,
        ]);
    }
}