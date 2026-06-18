<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TravelOrder\Support;

use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\TravelPeriod;

trait MakesTravelOrder
{
    protected function makeTravelOrder(
        ?string $id = null,
        int $userId = 1,
        string $requesterName = 'John Doe',
        string $destination = 'Paris',
        string $departureDate = '2026-08-01',
        string $returnDate = '2026-08-10',
        TravelOrderStatus $status = TravelOrderStatus::Solicitado,
    ): TravelOrder {
        return TravelOrder::reconstitute(
            id: $id ?? '550e8400-e29b-41d4-a716-446655440000',
            userId: $userId,
            requesterName: $requesterName,
            destination: $destination,
            period: TravelPeriod::fromStrings($departureDate, $returnDate),
            status: $status,
        );
    }

    protected function createTravelOrder(
        int $userId = 1,
        string $requesterName = 'John Doe',
        string $destination = 'Paris',
        string $departureDate = '2026-08-01',
        string $returnDate = '2026-08-10',
    ): TravelOrder {
        return TravelOrder::create(
            userId: $userId,
            requesterName: $requesterName,
            destination: $destination,
            period: TravelPeriod::fromStrings($departureDate, $returnDate),
        );
    }
}
