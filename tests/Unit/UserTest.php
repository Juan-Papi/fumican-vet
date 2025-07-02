<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic unit test example.
     */

    use RefreshDatabase;

    // prueba unitaria para verificar que el nombre completo del usuario se genere correctamente
    public function test_user_full_name_accessor_return_correct_format() 
    {
        $user = User::factory()->create([
            'first_name' => 'Ruben',
            'last_name' => 'Cano',
        ]);

        $this->assertEquals('Ruben Cano', $user->full_name);
    }

    public function test_example(): void
    {
        $this->assertTrue(true);
    }
}
