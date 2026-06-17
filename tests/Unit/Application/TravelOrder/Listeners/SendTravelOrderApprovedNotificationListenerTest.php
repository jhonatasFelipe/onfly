<?php

declare(strict_types=1);

namespace Tests\Unit\Application\TravelOrder\Listeners;

use App\Application\Ports\NotificationPort;
use App\Application\TravelOrder\Listeners\SendTravelOrderApprovedNotificationListener;
use App\Domain\TravelOrder\Events\TravelOrderApproved;
use App\Domain\TravelOrder\ValueObjects\TravelOrderId;
use App\Domain\TravelOrder\ValueObjects\UserId;
use Mockery;
use Tests\Unit\UnitTestCase;

final class SendTravelOrderApprovedNotificationListenerTest extends UnitTestCase
{
    public function test_notifies_approved_event(): void
    {
        $notifications = Mockery::mock(NotificationPort::class);
        $event = new TravelOrderApproved(
            TravelOrderId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            UserId::fromInt(1),
        );

        $notifications->shouldReceive('notifyApproved')
            ->once()
            ->with($event);

        $listener = new SendTravelOrderApprovedNotificationListener($notifications);

        $listener->handle($event);

        $this->addToAssertionCount(1);
    }
}
