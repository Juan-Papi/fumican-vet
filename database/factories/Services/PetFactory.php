<?php

namespace Database\Factories\Services;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Services\Pet>
 */
class PetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'color' => fake()->colorName(),
            'age' => fake()->numberBetween(1, 20),
            'photo_url' => fake()->imageUrl(),
        ];
    }
}
