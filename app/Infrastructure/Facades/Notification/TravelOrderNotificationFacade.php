<?php

declare(strict_types=1);

namespace App\Infrastructure\Facades\Notification;

use App\Domain\TravelOrder\Events\TravelOrderApproved;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;

/**
 * GoF Facade pattern — translates domain events to neutral notification payloads.
 */
final class TravelOrderNotificationFacade
{
    /**
     * @return array<string, mixed>
     */
    public function approvedPayload(TravelOrderApproved $event): array
    {
        return [
            'order_id' => $event->orderId->value(),
            'user_id' => $event->userId->value(),
            'status' => 'aprovado',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function cancelledPayload(TravelOrderCancelled $event): array
    {
        return [
            'order_id' => $event->orderId->value(),
            'user_id' => $event->userId->value(),
            'status' => 'cancelado',
        ];
    }
}
