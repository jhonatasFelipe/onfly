<?php

declare(strict_types=1);

namespace App\Infrastructure\Contracts;

use App\Domain\TravelOrder\Entities\TravelOrder;

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
