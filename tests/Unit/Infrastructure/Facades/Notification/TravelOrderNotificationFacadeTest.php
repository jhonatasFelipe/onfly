<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Facades\Notification;

use App\Domain\TravelOrder\Events\TravelOrderApproved;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;
use App\Domain\TravelOrder\ValueObjects\TravelOrderId;
use App\Domain\TravelOrder\ValueObjects\UserId;
use App\Infrastructure\Facades\Notification\TravelOrderNotificationFacade;
use PHPUnit\Framework\TestCase;

final class TravelOrderNotificationFacadeTest extends TestCase
{
    public function test_approved_payload_contains_expected_fields(): void
    {
        $facade = new TravelOrderNotificationFacade();
        $event = new TravelOrderApproved(
            TravelOrderId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            UserId::fromInt(7),
        );

        $payload = $facade->approvedPayload($event);

        $this->assertSame([
            'order_id' => '550e8400-e29b-41d4-a716-446655440000',
            'user_id' => 7,
            'status' => 'aprovado',
        ], $payload);
    }

    public function test_cancelled_payload_contains_expected_fields(): void
    {
        $facade = new TravelOrderNotificationFacade();
        $event = new TravelOrderCancelled(
            TravelOrderId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            UserId::fromInt(7),
        );

        $payload = $facade->cancelledPayload($event);

        $this->assertSame([
            'order_id' => '550e8400-e29b-41d4-a716-446655440000',
            'user_id' => 7,
            'status' => 'cancelado',
        ], $payload);
    }
}
