<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Adapters;

use App\Infrastructure\Adapters\SanctumAuthenticatedUserAdapter;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Tests\TestCase;

final class SanctumAuthenticatedUserAdapterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_id_returns_authenticated_user_id(): void
    {
        $user = UserModel::factory()->create();
        Auth::login($user);

        $adapter = new SanctumAuthenticatedUserAdapter;

        $this->assertSame($user->id, $adapter->userId());
    }

    public function test_requester_name_returns_authenticated_user_name(): void
    {
        $user = UserModel::factory()->create(['name' => 'Alice']);
        Auth::login($user);

        $adapter = new SanctumAuthenticatedUserAdapter;

        $this->assertSame('Alice', $adapter->requesterName());
    }

    public function test_is_admin_returns_authenticated_user_admin_flag(): void
    {
        $user = UserModel::factory()->admin()->create();
        Auth::login($user);

        $adapter = new SanctumAuthenticatedUserAdapter;

        $this->assertTrue($adapter->isAdmin());
    }

    public function test_throws_when_no_authenticated_user(): void
    {
        Auth::logout();

        $adapter = new SanctumAuthenticatedUserAdapter;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Authenticated user is required.');

        $adapter->userId();
    }
}
