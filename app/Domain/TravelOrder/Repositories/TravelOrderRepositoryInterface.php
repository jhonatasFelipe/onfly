<?php

declare(strict_types=1);

namespace App\Domain\TravelOrder\Repositories;

use App\Domain\TravelOrder\Collections\TravelOrderCollection;
use App\Domain\TravelOrder\Criteria\ListTravelOrdersCriteria;
use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\ValueObjects\TravelOrderId;

/**
 * Contrato de persistência de pedidos de viagem na linguagem do domínio.
 */
interface TravelOrderRepositoryInterface
{
    /**
     * Persiste o aggregate, criando ou atualizando conforme necessário.
     */
    public function save(TravelOrder $order): void;

    /**
     * Busca um pedido pelo identificador, ou null se não existir.
     */
    public function findById(TravelOrderId $id): ?TravelOrder;

    /**
     * Lista pedidos que atendem aos critérios informados.
     */
    public function list(ListTravelOrdersCriteria $criteria): TravelOrderCollection;
}
