<?php

declare(strict_types=1);

namespace App\Infrastructure\Facades\TravelOrder;

use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\ValueObjects\Destination;
use App\Domain\TravelOrder\ValueObjects\RequesterName;
use App\Domain\TravelOrder\ValueObjects\TravelOrderId;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\TravelPeriod;
use App\Domain\TravelOrder\ValueObjects\UserId;
use App\Infrastructure\Contracts\TravelOrderEloquentTranslatorInterface;

/**
 * Traduz registros Eloquent em entidades de domínio e vice-versa.
 */
final class TravelOrderEloquentTranslator implements TravelOrderEloquentTranslatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function toDomain(array $record): TravelOrder
    {
        return TravelOrder::reconstitute(
            id: TravelOrderId::fromString((string) $record['id']),
            userId: UserId::fromInt((int) $record['user_id']),
            requesterName: RequesterName::fromString((string) $record['requester_name']),
            destination: Destination::fromString((string) $record['destination']),
            period: TravelPeriod::fromStrings(
                (string) $record['departure_date'],
                (string) $record['return_date'],
            ),
            status: TravelOrderStatus::fromString((string) $record['status']),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toPersistenceArray(TravelOrder $order): array
    {
        return [
            'id' => $order->id()->value(),
            'user_id' => $order->userId()->value(),
            'requester_name' => $order->requesterName()->value(),
            'destination' => $order->destination()->value(),
            'departure_date' => $order->period()->departure->format('Y-m-d'),
            'return_date' => $order->period()->return->format('Y-m-d'),
            'status' => $order->status()->value,
        ];
    }
}
