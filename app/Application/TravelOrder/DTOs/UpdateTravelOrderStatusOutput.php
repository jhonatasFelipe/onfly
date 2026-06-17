<?php

declare(strict_types=1);

namespace App\Application\TravelOrder\DTOs;

use App\Domain\TravelOrder\Entities\TravelOrder;

final readonly class UpdateTravelOrderStatusOutput
{
    public function __construct(public TravelOrder $order) {}
}
