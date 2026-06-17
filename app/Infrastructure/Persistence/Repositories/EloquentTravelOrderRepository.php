<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Ports\TravelOrderPersistenceFacadeInterface;
use App\Domain\TravelOrder\Collections\TravelOrderCollection;
use App\Domain\TravelOrder\Criteria\ListTravelOrdersCriteria;
use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\Repositories\TravelOrderRepositoryInterface;
use App\Domain\TravelOrder\ValueObjects\TravelOrderId;
use App\Infrastructure\Contracts\TravelOrderListQueryPort;
use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;

/**
 * Implementação Eloquent do repositório de pedidos de viagem.
 */
final class EloquentTravelOrderRepository implements TravelOrderRepositoryInterface
{
    public function __construct(
        private readonly TravelOrderPersistenceFacadeInterface $facade,
        private readonly TravelOrderListQueryPort $listQuery,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function save(TravelOrder $order): void
    {
        TravelOrderModel::updateOrCreate(
            ['id' => $order->id()->value()],
            $this->facade->toPersistenceArray($order),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function findById(TravelOrderId $id): ?TravelOrder
    {
        $model = TravelOrderModel::find($id->value());

        return $model ? $this->facade->toDomain($model->getAttributes()) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function list(ListTravelOrdersCriteria $criteria): TravelOrderCollection
    {
        $query = TravelOrderModel::query();
        $query = $this->listQuery->apply($query, $criteria);

        $orders = $query->get()->map(
            fn (TravelOrderModel $model) => $this->facade->toDomain($model->getAttributes()),
        );

        return TravelOrderCollection::fromIterable($orders);
    }
}
