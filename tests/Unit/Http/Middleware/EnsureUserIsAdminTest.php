<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class EnsureUserIsAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_authenticated_admin(): void
    {
        $admin = UserModel::factory()->admin()->create();

        $middleware = new EnsureUserIsAdmin;
        $request = Request::create('/admin-only', 'GET');
        $request->setUserResolver(fn () => $admin);

        $response = $middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_forbids_non_admin_user(): void
    {
        $user = UserModel::factory()->create();

        $middleware = new EnsureUserIsAdmin;
        $request = Request::create('/admin-only', 'GET');
        $request->setUserResolver(fn () => $user);

        try {
            $middleware->handle($request, fn () => response('ok', 200));
            $this->fail('Expected HttpException was not thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_forbids_guest(): void
    {
        $middleware = new EnsureUserIsAdmin;
        $request = Request::create('/admin-only', 'GET');

        try {
            $middleware->handle($request, fn () => response('ok', 200));
            $this->fail('Expected HttpException was not thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }
}
