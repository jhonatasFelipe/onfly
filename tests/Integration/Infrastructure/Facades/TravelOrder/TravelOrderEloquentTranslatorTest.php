<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Facades\TravelOrder;

use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\ValueObjects\Destination;
use App\Domain\TravelOrder\ValueObjects\RequesterName;
use App\Domain\TravelOrder\ValueObjects\TravelOrderId;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\TravelPeriod;
use App\Domain\TravelOrder\ValueObjects\UserId;
use App\Infrastructure\Facades\TravelOrder\TravelOrderEloquentTranslator;
use PHPUnit\Framework\TestCase;

final class TravelOrderEloquentTranslatorTest extends TestCase
{
    public function test_round_trip_domain_to_array_and_back(): void
    {
        $translator = new TravelOrderEloquentTranslator();
        $order = TravelOrder::reconstitute(
            id: TravelOrderId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            userId: UserId::fromInt(1),
            requesterName: RequesterName::fromString('Alice'),
            destination: Destination::fromString('Lisboa'),
            period: TravelPeriod::fromStrings('2026-11-01', '2026-11-10'),
            status: TravelOrderStatus::Solicitado,
        );

        $record = $translator->toPersistenceArray($order);
        $restored = $translator->toDomain($record);

        $this->assertTrue($restored->id()->equals($order->id()));
        $this->assertSame('Lisboa', $restored->destination()->value());
        $this->assertSame(TravelOrderStatus::Solicitado, $restored->status());
    }
}
