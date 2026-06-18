<?php

declare(strict_types=1);

namespace Tests\Unit\Application\TravelOrder\Listeners;

use App\Application\Ports\NotificationPort;
use App\Application\TravelOrder\Listeners\SendTravelOrderCancelledNotificationListener;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;
use Mockery;
use Tests\Unit\UnitTestCase;

final class SendTravelOrderCancelledNotificationListenerTest extends UnitTestCase
{
    public function test_notifies_cancelled_event(): void
    {
        $this->expectNotToPerformAssertions();

        $notifications = Mockery::mock(NotificationPort::class);
        $event = new TravelOrderCancelled(
            '550e8400-e29b-41d4-a716-446655440000',
            1,
        );

        $notifications->shouldReceive('notifyCancelled')
            ->once()
            ->with($event);

        $listener = new SendTravelOrderCancelledNotificationListener($notifications);
        $listener->handle($event);
    }
}
