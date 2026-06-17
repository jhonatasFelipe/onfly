<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Adapters\Auth;

use App\Application\Auth\Exceptions\InvalidCredentialsException;
use App\Infrastructure\Adapters\Auth\EloquentUserAuthenticationAdapter;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class EloquentUserAuthenticationAdapterTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticates_valid_credentials(): void
    {
        $user = UserModel::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        $adapter = new EloquentUserAuthenticationAdapter;
        $result = $adapter->authenticate('user@example.com', 'password');

        $this->assertSame($user->id, $result->id);
        $this->assertSame('user@example.com', $result->email);
    }

    public function test_throws_for_invalid_credentials(): void
    {
        UserModel::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        $adapter = new EloquentUserAuthenticationAdapter;

        $this->expectException(InvalidCredentialsException::class);

        $adapter->authenticate('user@example.com', 'wrong-password');
    }

    public function test_throws_when_authenticated_user_is_not_user_model(): void
    {
        Auth::shouldReceive('attempt')
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn(null);

        Auth::shouldReceive('logout')
            ->once();

        $adapter = new EloquentUserAuthenticationAdapter;

        $this->expectException(InvalidCredentialsException::class);

        $adapter->authenticate('ghost@example.com', 'password');
    }
}
