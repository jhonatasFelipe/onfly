<?php

declare(strict_types=1);

namespace Tests\Unit\Support\RateLimit;

use App\Support\RateLimit\RouteRateLimitResolver;
use Tests\TestCase;

final class RouteRateLimitResolverTest extends TestCase
{
    private RouteRateLimitResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new RouteRateLimitResolver;
    }

    public function test_resolves_named_throttle_middleware_from_config(): void
    {
        config([
            'rate-limiting.auth' => [
                'max_attempts' => 10,
                'decay_minutes' => 1,
                'identifier' => 'IP do cliente',
            ],
            'rate-limiting.api' => [
                'max_attempts' => 60,
                'decay_minutes' => 1,
                'identifier' => 'usuário autenticado ou IP',
            ],
        ]);

        $limits = $this->resolver->resolveFromMiddleware([
            'throttle:api',
            'throttle:auth',
        ]);

        $this->assertCount(2, $limits);

        $byName = collect($limits)->keyBy('name');

        $this->assertSame(10, $byName['auth']['max_attempts']);
        $this->assertSame(60, $byName['api']['max_attempts']);
    }

    public function test_ignores_inline_throttle_definitions(): void
    {
        $limits = $this->resolver->resolveFromMiddleware(['throttle:10,1']);

        $this->assertSame([], $limits);
    }

    public function test_builds_description_and_extension_payload(): void
    {
        config([
            'rate-limiting.auth' => [
                'max_attempts' => 10,
                'decay_minutes' => 1,
                'identifier' => 'IP do cliente',
            ],
        ]);

        $limits = $this->resolver->resolveFromMiddleware(['throttle:auth']);

        $this->assertStringContainsString('**Rate limit**', $this->resolver->toDescriptionParagraph($limits));
        $this->assertStringContainsString('`auth`: 10 requisições por minuto por IP do cliente', $this->resolver->toDescriptionParagraph($limits));

        $this->assertSame([
            [
                'name' => 'auth',
                'limit' => 10,
                'period' => '1m',
                'by' => 'IP do cliente',
            ],
        ], $this->resolver->toExtensionPayload($limits));
    }
}
