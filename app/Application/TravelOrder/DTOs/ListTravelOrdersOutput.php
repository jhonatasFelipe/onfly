<?php

declare(strict_types=1);

namespace App\Application\TravelOrder\DTOs;

use App\Domain\TravelOrder\Collections\PaginatedTravelOrders;

final readonly class ListTravelOrdersOutput
{
    public function __construct(public PaginatedTravelOrders $page) {}
}
