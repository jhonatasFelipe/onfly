<?php

declare(strict_types=1);

namespace App\Application\TravelOrder\Listeners;

use App\Application\Ports\NotificationPort;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;

/**
 * Envia notificação ao solicitante quando um pedido de viagem é cancelado.
 */
final class SendTravelOrderCancelledNotificationListener
{
    public function __construct(
        private readonly NotificationPort $notifications,
    ) {}

    public function handle(TravelOrderCancelled $event): void
    {
        $this->notifications->notifyCancelled($event);
    }
}
