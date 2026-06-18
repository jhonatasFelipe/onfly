<?php

declare(strict_types=1);

namespace App\Domain\TravelOrder\Collections;

use App\Domain\Shared\ValueObjects\Pagination;

/**
 * Resultado paginado de pedidos de viagem na linguagem do domínio.
 */
final readonly class PaginatedTravelOrders
{
    public function __construct(
        public TravelOrderCollection $items,
        public int $total,
        public Pagination $pagination,
    ) {}

    public function lastPage(): int
    {
        return (int) max(1, (int) ceil($this->total / $this->pagination->perPage));
    }
}
