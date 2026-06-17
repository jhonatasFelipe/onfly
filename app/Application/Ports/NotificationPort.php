<?php

declare(strict_types=1);

namespace App\Application\Ports;

use App\Domain\TravelOrder\Events\TravelOrderApproved;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;

/**
 * Porta para envio de notificações relacionadas a pedidos de viagem.
 */
interface NotificationPort
{
    /**
     * Notifica o solicitante sobre aprovação do pedido.
     */
    public function notifyApproved(TravelOrderApproved $event): void;

    /**
     * Notifica o solicitante sobre cancelamento do pedido.
     */
    public function notifyCancelled(TravelOrderCancelled $event): void;
}
