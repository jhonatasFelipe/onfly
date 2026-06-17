<?php

declare(strict_types=1);

namespace App\Application\TravelOrder\Listeners;

use App\Application\Ports\NotificationPort;
use App\Domain\TravelOrder\Events\TravelOrderApproved;

/**
 * Envia notificação ao solicitante quando um pedido de viagem é aprovado.
 */
final class SendTravelOrderApprovedNotificationListener
{
    public function __construct(
        private readonly NotificationPort $notifications,
    ) {}

    public function handle(TravelOrderApproved $event): void
    {
        $this->notifications->notifyApproved($event);
    }
}
