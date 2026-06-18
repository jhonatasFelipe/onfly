<?php

declare(strict_types=1);

namespace App\Infrastructure\Facades\TravelOrder;

use App\Application\Ports\TravelOrderEloquentTranslatorInterface;
use App\Application\Ports\TravelOrderPersistenceFacadeInterface;
use App\Domain\TravelOrder\Entities\TravelOrder;

/**
 * GoF Facade pattern — not Illuminate\Support\Facades.
 *
 * Delega a tradução entre Eloquent e domínio para {@see TravelOrderEloquentTranslatorInterface}.
 */
final class TravelOrderPersistenceFacade implements TravelOrderPersistenceFacadeInterface
{
    public function __construct(
        private readonly TravelOrderEloquentTranslatorInterface $translator,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function toDomain(array $record): TravelOrder
    {
        return $this->translator->toDomain($record);
    }

    /**
     * {@inheritDoc}
     */
    public function toPersistenceArray(TravelOrder $order): array
    {
        return $this->translator->toPersistenceArray($order);
    }
}
