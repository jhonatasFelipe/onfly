<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Adapters;

use App\Domain\TravelOrder\Events\TravelOrderApproved;
use App\Domain\TravelOrder\ValueObjects\TravelOrderId;
use App\Domain\TravelOrder\ValueObjects\UserId;
use App\Infrastructure\Adapters\LaravelNotificationAdapter;
use App\Infrastructure\Facades\Notification\TravelOrderNotificationFacade;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Notifications\TravelOrderApprovedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class LaravelNotificationAdapterTest extends TestCase
{
    use RefreshDatabase;

    public function test_notify_approved_sends_notification_to_user(): void
    {
        Notification::fake();

        $user = UserModel::factory()->create();
        $adapter = new LaravelNotificationAdapter(new TravelOrderNotificationFacade());
        $event = new TravelOrderApproved(
            TravelOrderId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            UserId::fromInt($user->id),
        );

        $adapter->notifyApproved($event);

        Notification::assertSentTo($user, TravelOrderApprovedNotification::class);
    }

    public function test_notify_cancelled_sends_notification_to_user(): void
    {
        Notification::fake();

        $user = UserModel::factory()->create();
        $adapter = new LaravelNotificationAdapter(new TravelOrderNotificationFacade());
        $event = new \App\Domain\TravelOrder\Events\TravelOrderCancelled(
            TravelOrderId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            UserId::fromInt($user->id),
        );

        $adapter->notifyCancelled($event);

        Notification::assertSentTo($user, \App\Notifications\TravelOrderCancelledNotification::class);
    }
}
