<?php

namespace Database\Factories\Sales;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Sales\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sales\Medicament>
 */
class MedicamentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'dosage' => fake()->randomElement(['10mg', '20mg', '50mg', '100mg']),
            'manufacturer' => fake()->company(),
            'expiration_date' => fake()->dateTimeBetween('+1 year', '+5 years')->format('Y-m-d'),
            'controlled_substance' => fake()->randomElement(['yes', 'no']),
            'category_id' => Category::inRandomOrder()->first()->id,
        ];
    }
}
