<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\EnsureApiDocsAccess;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class EnsureApiDocsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_guest_access_in_local_environment(): void
    {
        $this->app['env'] = 'local';

        $middleware = new EnsureApiDocsAccess;
        $request = Request::create('/docs/api', 'GET');

        $response = $middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_allows_admin_in_restricted_environment(): void
    {
        $admin = UserModel::factory()->admin()->create();

        $middleware = new EnsureApiDocsAccess;
        $request = Request::create('/docs/api', 'GET');
        $request->setUserResolver(fn () => $admin);

        $response = $middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_forbids_guest_in_restricted_environment(): void
    {
        $middleware = new EnsureApiDocsAccess;
        $request = Request::create('/docs/api', 'GET');

        try {
            $middleware->handle($request, fn () => response('ok', 200));
            $this->fail('Expected HttpException was not thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }
}
