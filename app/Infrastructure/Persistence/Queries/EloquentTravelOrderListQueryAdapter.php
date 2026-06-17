<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Queries;

use App\Domain\TravelOrder\Criteria\ListTravelOrdersCriteria;
use App\Infrastructure\Contracts\TravelOrderListQueryPort;
use Illuminate\Database\Eloquent\Builder;

/**
 * Aplica critérios de listagem à query Eloquent de pedidos de viagem.
 */
final class EloquentTravelOrderListQueryAdapter implements TravelOrderListQueryPort
{
    /**
     * {@inheritDoc}
     */
    public function apply(Builder $query, ListTravelOrdersCriteria $criteria): Builder
    {
        if ($criteria->userId !== null) {
            $query->where('user_id', $criteria->userId->value());
        }

        if ($criteria->status !== null) {
            $query->where('status', $criteria->status->value);
        }

        if ($criteria->destination !== null && $criteria->destination !== '') {
            $query->where('destination', 'like', '%'.$criteria->destination.'%');
        }

        if ($criteria->createdFrom !== null) {
            $query->whereDate('created_at', '>=', $criteria->createdFrom);
        }

        if ($criteria->createdTo !== null) {
            $query->whereDate('created_at', '<=', $criteria->createdTo);
        }

        if ($criteria->departureFrom !== null) {
            $query->whereDate('departure_date', '>=', $criteria->departureFrom);
        }

        if ($criteria->departureTo !== null) {
            $query->whereDate('departure_date', '<=', $criteria->departureTo);
        }

        return $query->orderByDesc('created_at');
    }
}
