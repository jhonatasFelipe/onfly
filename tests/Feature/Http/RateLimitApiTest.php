<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class RateLimitApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('api');
        RateLimiter::clear('auth');
        RateLimiter::clear('web');
        RateLimiter::clear('web-login');
        RateLimiter::clear('docs');
    }

    public function test_login_endpoint_is_rate_limited(): void
    {
        config(['rate-limiting.auth.max_attempts' => 3]);

        UserModel::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'login@example.com',
                'password' => 'wrong-password',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429)
            ->assertJson(['message' => 'Muitas tentativas. Tente novamente mais tarde.']);
    }

    public function test_authenticated_api_endpoint_is_rate_limited(): void
    {
        config(['rate-limiting.api.max_attempts' => 3]);

        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        for ($i = 0; $i < 3; $i++) {
            $this->getJson('/api/v1/travel-orders')->assertOk();
        }

        $this->getJson('/api/v1/travel-orders')
            ->assertStatus(429)
            ->assertJson(['message' => 'Muitas tentativas. Tente novamente mais tarde.']);
    }

    public function test_admin_web_login_is_rate_limited(): void
    {
        config(['rate-limiting.web-login.max_attempts' => 3]);

        UserModel::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->from('/admin/login')
                ->post('/admin/login', [
                    'email' => 'admin@example.com',
                    'password' => 'wrong-password',
                ])
                ->assertRedirect('/admin/login');
        }

        $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])
            ->assertStatus(429);
    }

    public function test_api_documentation_is_rate_limited(): void
    {
        config(['rate-limiting.docs.max_attempts' => 3]);

        $admin = UserModel::factory()->admin()->create();

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($admin)->getJson('/docs/api.json')->assertOk();
        }

        $this->actingAs($admin)
            ->getJson('/docs/api.json')
            ->assertStatus(429);
    }
}
