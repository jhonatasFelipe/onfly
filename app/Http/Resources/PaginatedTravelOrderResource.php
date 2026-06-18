<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\TravelOrder\Collections\PaginatedTravelOrders;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formata resultado paginado de pedidos de viagem para resposta JSON da API.
 */
final class PaginatedTravelOrderResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var PaginatedTravelOrders $page */
        $page = $this->resource;

        return [
            'data' => TravelOrderResource::collection(collect($page->items->all()))->resolve($request),
            'meta' => [
                'current_page' => $page->pagination->page,
                'per_page' => $page->pagination->perPage,
                'total' => $page->total,
                'last_page' => $page->lastPage(),
            ],
        ];
    }
}
