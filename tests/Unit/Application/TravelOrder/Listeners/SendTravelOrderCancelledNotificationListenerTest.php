<?php

declare(strict_types=1);

namespace Tests\Unit\Application\TravelOrder\Listeners;

use App\Application\Ports\NotificationPort;
use App\Application\TravelOrder\Listeners\SendTravelOrderCancelledNotificationListener;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;
use App\Domain\TravelOrder\ValueObjects\TravelOrderId;
use App\Domain\TravelOrder\ValueObjects\UserId;
use Mockery;
use Tests\Unit\UnitTestCase;

final class SendTravelOrderCancelledNotificationListenerTest extends UnitTestCase
{
    public function test_notifies_cancelled_event(): void
    {
        $notifications = Mockery::mock(NotificationPort::class);
        $event = new TravelOrderCancelled(
            TravelOrderId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            UserId::fromInt(1),
        );

        $notifications->shouldReceive('notifyCancelled')
            ->once()
            ->with($event);

        $listener = new SendTravelOrderCancelledNotificationListener($notifications);

        $listener->handle($event);

        $this->addToAssertionCount(1);
    }
}
