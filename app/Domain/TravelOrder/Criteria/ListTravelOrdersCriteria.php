<?php

declare(strict_types=1);

namespace App\Domain\TravelOrder\Criteria;

use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\UserId;

/**
 * Critérios de filtragem para listagem de pedidos de viagem.
 */
final readonly class ListTravelOrdersCriteria
{
    public function __construct(
        public ?UserId $userId = null,
        public ?TravelOrderStatus $status = null,
        public ?string $destination = null,
        public ?string $createdFrom = null,
        public ?string $createdTo = null,
        public ?string $departureFrom = null,
        public ?string $departureTo = null,
    ) {}
}
