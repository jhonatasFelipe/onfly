<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Facades\Notification;

use App\Domain\TravelOrder\Events\TravelOrderApproved;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;
use App\Infrastructure\Facades\Notification\TravelOrderNotificationFacade;
use PHPUnit\Framework\TestCase;

final class TravelOrderNotificationFacadeTest extends TestCase
{
    public function test_approved_payload_contains_expected_fields(): void
    {
        $event = new TravelOrderApproved(
            '550e8400-e29b-41d4-a716-446655440000',
            7,
        );

        $payload = (new TravelOrderNotificationFacade)->approvedPayload($event);

        $this->assertSame([
            'order_id' => '550e8400-e29b-41d4-a716-446655440000',
            'user_id' => 7,
            'status' => 'aprovado',
        ], $payload);
    }

    public function test_cancelled_payload_contains_expected_fields(): void
    {
        $event = new TravelOrderCancelled(
            '550e8400-e29b-41d4-a716-446655440000',
            7,
        );

        $payload = (new TravelOrderNotificationFacade)->cancelledPayload($event);

        $this->assertSame([
            'order_id' => '550e8400-e29b-41d4-a716-446655440000',
            'user_id' => 7,
            'status' => 'cancelado',
        ], $payload);
    }
}
