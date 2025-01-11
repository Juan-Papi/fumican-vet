<?php

namespace Database\Seeders;

use App\Models\Services\Specie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpecieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $species = [
            'Canine',
            'Feline',
            'Rodent',
            'Bird',
            'Reptile',
            'Amphibian',
            'Fish',
            'Invertebrate',
        ];

        foreach ($species as $specie) {
            Specie::create(['name' => $specie]);
        }
    }
}
