<?php

namespace Database\Seeders;

use App\Models\Services\Breed;
use App\Models\Services\Specie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BreedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $species = Specie::all();
        foreach ($species as $specie) {
            Breed::factory()
                ->count(3)
                ->create([
                    'specie_id' => $specie->id
                ]);
        }
    }
}
