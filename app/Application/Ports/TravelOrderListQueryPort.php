<?php

declare(strict_types=1);

namespace App\Application\Ports;

use App\Domain\TravelOrder\Collections\PaginatedTravelOrders;
use App\Domain\TravelOrder\Criteria\ListTravelOrdersCriteria;

/**
 * Porta de consulta para listagem paginada de pedidos de viagem.
 */
interface TravelOrderListQueryPort
{
    public function paginate(ListTravelOrdersCriteria $criteria): PaginatedTravelOrders;
}
