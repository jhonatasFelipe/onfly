<?php

declare(strict_types=1);

namespace App\Application\Ports;

use App\Domain\TravelOrder\Entities\TravelOrder;

/**
 * Porta para tradução entre registros de persistência e entidades de domínio.
 */
interface TravelOrderEloquentTranslatorInterface
{
    /**
     * @param  array<string, mixed>  $record
     */
    public function toDomain(array $record): TravelOrder;

    /**
     * @return array<string, mixed>
     */
    public function toPersistenceArray(TravelOrder $order): array;
}
