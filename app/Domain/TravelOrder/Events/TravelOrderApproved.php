<?php

declare(strict_types=1);

namespace App\Domain\TravelOrder\Events;

use App\Domain\TravelOrder\ValueObjects\TravelOrderId;
use App\Domain\TravelOrder\ValueObjects\UserId;

/**
 * Evento de domínio emitido quando um pedido de viagem é aprovado.
 */
final readonly class TravelOrderApproved
{
    public function __construct(
        public TravelOrderId $orderId,
        public UserId $userId,
    ) {}
}
