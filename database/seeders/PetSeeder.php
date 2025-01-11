<?php

namespace Database\Seeders;

use App\Models\Services\Breed;
use App\Models\Services\Customer;
use App\Models\Services\Pet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            SpecieSeeder::class,
            BreedSeeder::class,
            CustomerSeeder::class,
        ]);

        $breeds = Breed::all();
        $customers = Customer::all();

        Pet::factory()
            ->count(50)
            ->make()
            ->each(function ($pet) use ($breeds, $customers) {
                $pet->breed_id = $breeds->random()->id;
                $pet->customer_id = $customers->random()->id;
                $pet->save();
            });
    }
}
