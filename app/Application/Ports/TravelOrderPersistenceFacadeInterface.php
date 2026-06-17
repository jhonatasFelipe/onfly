<?php

declare(strict_types=1);

namespace App\Application\Ports;

use App\Domain\TravelOrder\Entities\TravelOrder;

/**
 * Facade de persistência — traduz entre registros Eloquent e entidades de domínio.
 */
interface TravelOrderPersistenceFacadeInterface
{
    /**
     * Converte um registro de persistência em entidade de domínio.
     *
     * @param  array<string, mixed>  $record
     */
    public function toDomain(array $record): TravelOrder;

    /**
     * Converte uma entidade de domínio em array para persistência.
     *
     * @return array<string, mixed>
     */
    public function toPersistenceArray(TravelOrder $order): array;
}
