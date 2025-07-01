<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_login_returns_token()
    {
        $user = User::factory()->create(['password' => bcrypt('123456')]);

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => '123456',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['token']);
    }

    use RefreshDatabase; // Limpia la base de datos entre tests

    // prueba de integración para verificar que un usuario se pueda crear correctamente
    public function test_user_can_be_created()
    {
        // Preparar datos
        $userData = [
            'first_name' => 'Ruben',
            'last_name' => 'Cano',
            'email' => 'ruben@ejemplo.com',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'profile_photo_path' => null,
            'current_team_id' => null,
        ];

        // Crear usuario
        $user = User::create($userData);

        // Asegurar que fue creado en la base de datos
        $this->assertDatabaseHas('users', [
            'email' => 'ruben@ejemplo.com',
            'first_name' => 'Ruben',
            'last_name' => 'Cano',
            'password' => $user->password, // Verifica que la contraseña se haya guardado correctamente
        ]);
    }       
}
