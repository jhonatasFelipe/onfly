<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Queries;

use App\Application\Ports\TravelOrderListQueryPort;
use App\Application\Ports\TravelOrderPersistenceFacadeInterface;
use App\Domain\TravelOrder\Collections\PaginatedTravelOrders;
use App\Domain\TravelOrder\Collections\TravelOrderCollection;
use App\Domain\TravelOrder\Criteria\ListTravelOrdersCriteria;
use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Implementação Eloquent da consulta paginada de pedidos de viagem.
 */
final class EloquentTravelOrderListQueryAdapter implements TravelOrderListQueryPort
{
    public function __construct(
        private readonly TravelOrderPersistenceFacadeInterface $facade,
    ) {}

    public function paginate(ListTravelOrdersCriteria $criteria): PaginatedTravelOrders
    {
        $query = $this->applyFilters(TravelOrderModel::query(), $criteria);
        $total = (clone $query)->count();

        $models = $query
            ->orderByDesc('created_at')
            ->offset($criteria->pagination->offset())
            ->limit($criteria->pagination->perPage)
            ->get();

        $orders = $models->map(
            fn (TravelOrderModel $model) => $this->facade->toDomain($model->getAttributes()),
        );

        return new PaginatedTravelOrders(
            items: TravelOrderCollection::fromIterable($orders),
            total: $total,
            pagination: $criteria->pagination,
        );
    }

    /**
     * @param  Builder<TravelOrderModel>  $query
     * @return Builder<TravelOrderModel>
     */
    private function applyFilters(Builder $query, ListTravelOrdersCriteria $criteria): Builder
    {
        if ($criteria->userId !== null) {
            $query->where('user_id', $criteria->userId);
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

        return $query;
    }
}
