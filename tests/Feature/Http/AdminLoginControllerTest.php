<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminLoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_is_displayed(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertViewIs('admin.login');
    }

    public function test_admin_can_logout(): void
    {
        $admin = UserModel::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_admin_login_rejects_invalid_credentials(): void
    {
        UserModel::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
