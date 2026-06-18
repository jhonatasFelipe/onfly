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
     * @return array{order_id: string, user_id: int, status: string}
     */
    public function approvedPayload(TravelOrderApproved $event): array
    {
        return [
            'order_id' => $event->orderId,
            'user_id' => $event->userId,
            'status' => 'aprovado',
        ];
    }

    /**
     * @return array{order_id: string, user_id: int, status: string}
     */
    public function cancelledPayload(TravelOrderCancelled $event): array
    {
        return [
            'order_id' => $event->orderId,
            'user_id' => $event->userId,
            'status' => 'cancelado',
        ];
    }
}
