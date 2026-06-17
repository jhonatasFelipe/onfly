<?php

declare(strict_types=1);

namespace App\Http\OpenApi\Scramble;

use App\Support\RateLimit\RouteRateLimitResolver;
use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\RouteInfo;

/**
 * Documenta os rate limits de cada endpoint na spec OpenAPI gerada pelo Scramble.
 */
final class RateLimitOperationExtension extends OperationExtension
{
    public function handle(Operation $operation, RouteInfo $routeInfo): void
    {
        $resolver = new RouteRateLimitResolver;
        $limits = $resolver->resolveFromRoute($routeInfo->route);

        if ($limits === []) {
            return;
        }

        $rateLimitBlock = $resolver->toDescriptionParagraph($limits);

        $operation->description = $operation->description === ''
            ? $rateLimitBlock
            : rtrim($operation->description)."\n\n".$rateLimitBlock;

        $operation->setExtensionProperty('rateLimit', $resolver->toExtensionPayload($limits));

        $this->documentTooManyRequestsResponse($operation, $limits);
    }

    /**
     * @param  list<array{name: string, max_attempts: int, decay_minutes: int, identifier: string, period_label: string}>  $limits
     */
    private function documentTooManyRequestsResponse(Operation $operation, array $limits): void
    {
        $description = 'Limite de requisições excedido. Limites aplicados a este endpoint: '.collect($limits)
            ->map(fn (array $limit): string => sprintf(
                '`%s` (%d/%s)',
                $limit['name'],
                $limit['max_attempts'],
                $limit['period_label'],
            ))
            ->implode(', ');

        $response = $this->findResponse($operation, 429);

        if ($response === null) {
            $body = new ObjectType;
            $body->addProperty('message', (new StringType)->setDescription('Mensagem de erro'));

            $response = Response::make(429)
                ->setDescription($description)
                ->setContent('application/json', Schema::fromType($body));

            $operation->addResponse($response);

            return;
        }

        $response->setDescription($description);
    }

    private function findResponse(Operation $operation, int $statusCode): ?Response
    {
        foreach ($operation->responses ?? [] as $response) {
            if ($response instanceof Response && (int) $response->code === $statusCode) {
                return $response;
            }
        }

        return null;
    }
}
