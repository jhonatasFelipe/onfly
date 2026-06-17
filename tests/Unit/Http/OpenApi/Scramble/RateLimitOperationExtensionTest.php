<?php

declare(strict_types=1);

namespace Tests\Unit\Http\OpenApi\Scramble;

use App\Http\OpenApi\Scramble\RateLimitOperationExtension;
use Dedoc\Scramble\GeneratorConfig;
use Dedoc\Scramble\Infer;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\TypeTransformer;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Routing\Route;
use Tests\TestCase;

final class RateLimitOperationExtensionTest extends TestCase
{
    private RateLimitOperationExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new RateLimitOperationExtension(
            $this->createMock(Infer::class),
            $this->createMock(TypeTransformer::class),
            new GeneratorConfig,
        );
    }

    public function test_skips_operations_without_throttle_middleware(): void
    {
        $routeInfo = $this->routeInfoWithMiddleware([]);
        $operation = Operation::make('get');
        $operation->description = 'Original';

        $this->extension->handle($operation, $routeInfo);

        $this->assertSame('Original', $operation->description);
        $this->assertNull($operation->getExtensionProperty('rateLimit'));
    }

    public function test_uses_rate_limit_block_as_description_when_empty(): void
    {
        $routeInfo = $this->routeInfoWithMiddleware(['throttle:auth']);
        $operation = Operation::make('post');
        $operation->description = '';

        $this->extension->handle($operation, $routeInfo);

        $this->assertSame('**Rate limit**', strtok($operation->description, "\n"));
        $this->assertNotEmpty($operation->getExtensionProperty('rateLimit'));
    }

    public function test_appends_rate_limit_block_to_existing_description(): void
    {
        $routeInfo = $this->routeInfoWithMiddleware(['throttle:api']);
        $operation = Operation::make('get');
        $operation->description = 'Descrição existente';

        $this->extension->handle($operation, $routeInfo);

        $this->assertStringStartsWith('Descrição existente', $operation->description);
        $this->assertStringContainsString('**Rate limit**', $operation->description);
    }

    public function test_creates_429_response_when_missing(): void
    {
        $routeInfo = $this->routeInfoWithMiddleware(['throttle:api']);
        $operation = Operation::make('get');

        $this->extension->handle($operation, $routeInfo);

        $response = $this->findResponseByCode($operation, 429);

        $this->assertNotNull($response);
        $this->assertStringContainsString('Limite de requisições excedido', $response->description);
    }

    public function test_updates_existing_429_response_description(): void
    {
        $routeInfo = $this->routeInfoWithMiddleware(['throttle:auth']);
        $operation = Operation::make('post');
        $operation->addResponse(Response::make(429)->setDescription('Descrição antiga'));

        $this->extension->handle($operation, $routeInfo);

        $response = $this->findResponseByCode($operation, 429);

        $this->assertNotNull($response);
        $this->assertStringContainsString('Limite de requisições excedido', $response->description);
        $this->assertStringNotContainsString('Descrição antiga', $response->description);
    }

    /**
     * @param  list<string>  $middleware
     */
    private function routeInfoWithMiddleware(array $middleware): RouteInfo
    {
        $route = $this->createMock(Route::class);
        $route->method('gatherMiddleware')->willReturn($middleware);

        return new RouteInfo($route, 'GET');
    }

    private function findResponseByCode(Operation $operation, int $statusCode): ?Response
    {
        foreach ($operation->responses ?? [] as $response) {
            if ($response instanceof Response && (int) $response->code === $statusCode) {
                return $response;
            }
        }

        return null;
    }
}
