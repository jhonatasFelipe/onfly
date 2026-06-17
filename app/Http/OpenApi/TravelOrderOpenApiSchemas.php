<?php

declare(strict_types=1);

namespace App\Http\OpenApi;

/**
 * Schemas OpenAPI reutilizáveis para pedidos de viagem.
 */
final class TravelOrderOpenApiSchemas
{
    public const ITEM = 'array{id: string, requester_name: string, destination: string, departure_date: string, return_date: string, status: string}';

    public const LIST = 'array{data: array<int, array{id: string, requester_name: string, destination: string, departure_date: string, return_date: string, status: string}>}';
}
