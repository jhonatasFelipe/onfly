<?php

declare(strict_types=1);

namespace App\Application\TravelOrder\UseCases;

use App\Application\Ports\EventDispatcherPort;
use App\Application\TravelOrder\DTOs\UpdateTravelOrderStatusInput;
use App\Application\TravelOrder\DTOs\UpdateTravelOrderStatusOutput;
use App\Domain\TravelOrder\Exceptions\InvalidTravelOrderStateException;
use App\Domain\TravelOrder\Exceptions\TravelOrderNotFoundException;
use App\Domain\TravelOrder\Repositories\TravelOrderRepositoryInterface;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;

/**
 * Atualiza o status de um pedido de viagem (aprovação ou cancelamento) e dispara eventos.
 */
final class UpdateTravelOrderStatusUseCase
{
    public function __construct(
        private readonly TravelOrderRepositoryInterface $orders,
        private readonly EventDispatcherPort $events,
    ) {}

    /**
     * @throws TravelOrderNotFoundException
     * @throws InvalidTravelOrderStateException
     */
    public function execute(UpdateTravelOrderStatusInput $input): UpdateTravelOrderStatusOutput
    {
        $order = $this->orders->findById($input->orderId)
            ?? throw new TravelOrderNotFoundException('Travel order not found.');

        $targetStatus = TravelOrderStatus::fromString($input->status);

        match ($targetStatus) {
            TravelOrderStatus::Aprovado => $order->approve(),
            TravelOrderStatus::Cancelado => $order->cancel(),
            TravelOrderStatus::Solicitado => throw new InvalidTravelOrderStateException('Cannot revert to requested status.'),
        };

        $this->orders->save($order);

        foreach ($order->pullDomainEvents() as $event) {
            $this->events->dispatch($event);
        }

        return new UpdateTravelOrderStatusOutput($order);
    }
}
