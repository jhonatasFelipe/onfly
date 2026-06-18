<?php

declare(strict_types=1);

namespace App\Domain\TravelOrder\Repositories;

use App\Domain\TravelOrder\Entities\TravelOrder;

/**
 * Contrato de persistência de pedidos de viagem na linguagem do domínio.
 */
interface TravelOrderRepositoryInterface
{
    public function save(TravelOrder $order): void;

    public function findById(string $id): ?TravelOrder;
}
