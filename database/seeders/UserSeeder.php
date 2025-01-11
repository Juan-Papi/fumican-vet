<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // \App\Models\User::factory()->create([
        //     'id' => Str::uuid()->toString(),
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        //     'password' => bcrypt('12345678'),
        // ]);

        $user1 = User::create([
            'first_name' => 'Juan Pablo',
            'last_name' => 'Rodriguez',
            'email' => 'juancho123sc@gmail.com',
            'password' => bcrypt('12345678'),
        ]);

        $user2 = User::create([
            'first_name' => 'Jose Armando',
            'last_name' => 'Gutierrez Lopez',
            'email' => 'armando@gmail.com',
            'password' => bcrypt('12345678'),
        ]);

        $user1->assignRole(RoleEnum::GERENTE_PROPIETARIO->value);
        $user2->assignRole(RoleEnum::GERENTE_PROPIETARIO->value);

        $users = User::factory()->count(10)->create();
        $roles = array_filter(RoleEnum::cases(), fn($role) => $role !== RoleEnum::GERENTE_PROPIETARIO);

        foreach ($users as $user) {
            $randomRole = $roles[array_rand($roles)];
            $user->assignRole($randomRole->value);
        }
    }
}
