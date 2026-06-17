<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Auth;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()->assertJsonStructure(['token', 'user']);
    }

    public function test_register_rejects_invalid_payload(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ])->assertUnprocessable();
    }

    public function test_register_rejects_duplicate_email(): void
    {
        UserModel::factory()->create(['email' => 'dup@example.com']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Another User',
            'email' => 'dup@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertUnprocessable();
    }

    public function test_user_can_login(): void
    {
        UserModel::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['token']);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        UserModel::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_user_can_logout(): void
    {
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/auth/logout')->assertOk();
    }
}
