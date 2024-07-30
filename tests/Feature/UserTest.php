<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_create_user(): void
    {
        $response = $this->postJson('/api/user',[
            "name" => "Jhonatas Felipe",
            "email" =>  "jhonatas1021@gmail.com",
            "password" => "Klapaucius1*",
            "password_confirmation" => "Klapaucius1*"

        ]);

        $response->assertStatus(201);

        $response->assertJson([
            'name' => "Jhonatas Felipe",
            'email' => "jhonatas1021@gmail.com",
            'email_verified_at' => NULL,
        ]);
    }


    public function test_get_user(): void
    {
        $this->seed(UserSeeder::class);

        $user = User::find(1);

        $response = $this->withHeader('Accept', 'Application/json')
        ->actingAs($user)
        ->get('/api/user');

        $response->assertStatus(200);

        $response->assertJson([
            'name' => "user teste1",
            'email' => "userteste1@teste.com.br",
            'email_verified_at' => NULL,
        ]);
    }
}
