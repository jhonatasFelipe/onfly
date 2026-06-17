<?php

declare(strict_types=1);

namespace App\Application\TravelOrder\DTOs;

final readonly class ShowTravelOrderInput
{
    public function __construct(public string $orderId) {}
}
