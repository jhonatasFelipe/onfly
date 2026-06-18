<?php

declare(strict_types=1);

namespace App\Domain\TravelOrder\Events;

/**
 * Evento de domínio emitido quando um pedido de viagem é aprovado.
 */
final readonly class TravelOrderApproved
{
    public function __construct(
        public string $orderId,
        public int $userId,
    ) {}
}
