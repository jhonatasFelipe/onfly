<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters;

use App\Application\Ports\NotificationPort;
use App\Domain\TravelOrder\Events\TravelOrderApproved;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;
use App\Infrastructure\Facades\Notification\TravelOrderNotificationFacade;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Notifications\TravelOrderApprovedNotification;
use App\Notifications\TravelOrderCancelledNotification;

/**
 * Implementação Laravel de {@see NotificationPort} via Notifications.
 */
final class LaravelNotificationAdapter implements NotificationPort
{
    public function __construct(
        private readonly TravelOrderNotificationFacade $notificationFacade,
    ) {}

    public function notifyApproved(TravelOrderApproved $event): void
    {
        $user = UserModel::findOrFail($event->userId->value());
        $payload = $this->notificationFacade->approvedPayload($event);

        $user->notify(new TravelOrderApprovedNotification($payload));
    }

    public function notifyCancelled(TravelOrderCancelled $event): void
    {
        $user = UserModel::findOrFail($event->userId->value());
        $payload = $this->notificationFacade->cancelledPayload($event);

        $user->notify(new TravelOrderCancelledNotification($payload));
    }
}
