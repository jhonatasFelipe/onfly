<?php

declare(strict_types=1);

namespace App\Application\TravelOrder\DTOs;

final readonly class ListTravelOrdersInput
{
    public function __construct(
        public int $page,
        public int $perPage,
        public ?string $status = null,
        public ?string $destination = null,
        public ?string $createdFrom = null,
        public ?string $createdTo = null,
        public ?string $departureFrom = null,
        public ?string $departureTo = null,
    ) {}
}
