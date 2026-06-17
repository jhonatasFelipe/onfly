<?php

declare(strict_types=1);

namespace App\Support\RateLimit;

use Illuminate\Routing\Route;

/**
 * Resolve limites de rate limiting aplicados a uma rota a partir do middleware throttle.
 */
final class RouteRateLimitResolver
{
    /**
     * @return list<array{
     *     name: string,
     *     max_attempts: int,
     *     decay_minutes: int,
     *     identifier: string,
     *     period_label: string
     * }>
     */
    public function resolveFromRoute(Route $route): array
    {
        return $this->resolveFromMiddleware($route->gatherMiddleware());
    }

    /**
     * @param  list<mixed>  $middleware
     * @return list<array{
     *     name: string,
     *     max_attempts: int,
     *     decay_minutes: int,
     *     identifier: string,
     *     period_label: string
     * }>
     */
    public function resolveFromMiddleware(array $middleware): array
    {
        $limits = [];

        foreach ($middleware as $entry) {
            if (! is_string($entry)) {
                continue;
            }

            $name = $this->extractLimiterName($entry);

            if ($name === null) {
                continue;
            }

            /** @var array{max_attempts?: int, decay_minutes?: int, identifier?: string}|null $config */
            $config = config("rate-limiting.{$name}");

            if ($config === null || ! isset($config['max_attempts'], $config['decay_minutes'])) {
                continue;
            }

            if ($this->containsLimit($limits, $name)) {
                continue;
            }

            $decayMinutes = (int) $config['decay_minutes'];

            $limits[] = [
                'name' => $name,
                'max_attempts' => (int) $config['max_attempts'],
                'decay_minutes' => $decayMinutes,
                'identifier' => (string) ($config['identifier'] ?? 'IP do cliente'),
                'period_label' => $this->periodLabel($decayMinutes),
            ];
        }

        return $limits;
    }

    /**
     * @param  list<array{name: string, max_attempts: int, decay_minutes: int, identifier: string, period_label: string}>  $limits
     */
    public function toDescriptionParagraph(array $limits): string
    {
        if ($limits === []) {
            return '';
        }

        $lines = array_map(
            fn (array $limit): string => sprintf(
                '- `%s`: %d requisições por %s por %s',
                $limit['name'],
                $limit['max_attempts'],
                $limit['period_label'],
                $limit['identifier'],
            ),
            $limits,
        );

        return "**Rate limit**\n\n".implode("\n", $lines);
    }

    /**
     * @param  list<array{name: string, max_attempts: int, decay_minutes: int, identifier: string, period_label: string}>  $limits
     * @return list<array{name: string, limit: int, period: string, by: string}>
     */
    public function toExtensionPayload(array $limits): array
    {
        return array_map(
            fn (array $limit): array => [
                'name' => $limit['name'],
                'limit' => $limit['max_attempts'],
                'period' => $this->periodIso($limit['decay_minutes']),
                'by' => $limit['identifier'],
            ],
            $limits,
        );
    }

    private function extractLimiterName(string $middleware): ?string
    {
        if (! str_starts_with($middleware, 'throttle:')) {
            return null;
        }

        $payload = substr($middleware, strlen('throttle:'));

        if ($payload === '' || str_contains($payload, ',')) {
            return null;
        }

        return $payload;
    }

    /**
     * @param  list<array{name: string, max_attempts: int, decay_minutes: int, identifier: string, period_label: string}>  $limits
     */
    private function containsLimit(array $limits, string $name): bool
    {
        foreach ($limits as $limit) {
            if ($limit['name'] === $name) {
                return true;
            }
        }

        return false;
    }

    private function periodLabel(int $decayMinutes): string
    {
        return match (true) {
            $decayMinutes === 1 => 'minuto',
            $decayMinutes % 60 === 0 && intdiv($decayMinutes, 60) === 1 => 'hora',
            $decayMinutes % 60 === 0 => intdiv($decayMinutes, 60).' horas',
            default => "{$decayMinutes} minutos",
        };
    }

    private function periodIso(int $decayMinutes): string
    {
        return match (true) {
            $decayMinutes === 1 => '1m',
            $decayMinutes % 60 === 0 => intdiv($decayMinutes, 60).'h',
            default => "{$decayMinutes}m",
        };
    }
}
