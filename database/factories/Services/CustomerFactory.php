<?php

namespace Database\Factories\Services;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Services\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'ci' => fake()->unique()->randomNumber(8),
            'phone_number' => fake()->phoneNumber(),
            'gender' => fake()->numberBetween(0, 2),
            'birthdate' => fake()->date('Y-m-d', '-18 years'),
        ];
    }
}
