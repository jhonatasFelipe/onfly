<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Adapters\Auth;

use App\Infrastructure\Adapters\Auth\SanctumApiTokenAdapter;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

final class SanctumApiTokenAdapterTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_token_for_user(): void
    {
        $user = UserModel::factory()->create();

        $adapter = new SanctumApiTokenAdapter;
        $token = $adapter->createForUser($user->id, 'api');

        $this->assertNotEmpty($token);
    }

    public function test_revokes_current_token_for_authenticated_user(): void
    {
        $user = UserModel::factory()->create();
        $accessToken = $user->createToken('api');

        Auth::setUser($user->withAccessToken($accessToken->accessToken));

        $adapter = new SanctumApiTokenAdapter;
        $adapter->revokeCurrent();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $accessToken->accessToken->id,
        ]);
    }

    public function test_revoke_current_throws_without_authenticated_user(): void
    {
        Auth::shouldReceive('user')
            ->once()
            ->andReturn(null);

        $adapter = new SanctumApiTokenAdapter;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Authenticated user is required.');

        $adapter->revokeCurrent();
    }
}
