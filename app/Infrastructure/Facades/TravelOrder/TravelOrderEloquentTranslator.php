<?php

declare(strict_types=1);

namespace App\Infrastructure\Facades\TravelOrder;

use App\Application\Ports\TravelOrderEloquentTranslatorInterface;
use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\TravelPeriod;
use InvalidArgumentException;

/**
 * Traduz registros Eloquent em entidades de domínio e vice-versa.
 */
final class TravelOrderEloquentTranslator implements TravelOrderEloquentTranslatorInterface
{
    /**
     * @param  array<string, mixed>  $record
     */
    public function toDomain(array $record): TravelOrder
    {
        return TravelOrder::reconstitute(
            id: self::stringValue($record, 'id'),
            userId: self::intValue($record, 'user_id'),
            requesterName: self::stringValue($record, 'requester_name'),
            destination: self::stringValue($record, 'destination'),
            period: TravelPeriod::fromStrings(
                self::stringValue($record, 'departure_date'),
                self::stringValue($record, 'return_date'),
            ),
            status: TravelOrderStatus::fromString(self::stringValue($record, 'status')),
        );
    }

    public function toPersistenceArray(TravelOrder $order): array
    {
        return [
            'id' => $order->id(),
            'user_id' => $order->userId(),
            'requester_name' => $order->requesterName(),
            'destination' => $order->destination(),
            'departure_date' => $order->period()->departure->format('Y-m-d'),
            'return_date' => $order->period()->return->format('Y-m-d'),
            'status' => $order->status()->value,
        ];
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private static function stringValue(array $record, string $key): string
    {
        $value = $record[$key] ?? null;

        if (! is_string($value)) {
            throw new InvalidArgumentException("Expected string for {$key}.");
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private static function intValue(array $record, string $key): int
    {
        $value = $record[$key] ?? null;

        if (! is_int($value)) {
            throw new InvalidArgumentException("Expected int for {$key}.");
        }

        return $value;
    }
}
