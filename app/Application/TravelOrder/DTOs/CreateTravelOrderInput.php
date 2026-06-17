<?php

declare(strict_types=1);

namespace App\Application\TravelOrder\DTOs;

final readonly class CreateTravelOrderInput
{
    public function __construct(
        public string $destination,
        public string $departureDate,
        public string $returnDate,
    ) {}
}
