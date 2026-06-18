<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Ports\TravelOrderPersistenceFacadeInterface;
use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\Repositories\TravelOrderRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;

/**
 * Implementação Eloquent do repositório de pedidos de viagem.
 */
final class EloquentTravelOrderRepository implements TravelOrderRepositoryInterface
{
    public function __construct(
        private readonly TravelOrderPersistenceFacadeInterface $facade,
    ) {}

    public function save(TravelOrder $order): void
    {
        TravelOrderModel::updateOrCreate(
            ['id' => $order->id()],
            $this->facade->toPersistenceArray($order),
        );
    }

    public function findById(string $id): ?TravelOrder
    {
        $model = TravelOrderModel::find($id);

        return $model ? $this->facade->toDomain($model->getAttributes()) : null;
    }
}
