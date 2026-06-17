<?php

declare(strict_types=1);

namespace App\Application\TravelOrder\UseCases;

use App\Application\Ports\AuthenticatedUserPort;
use App\Application\TravelOrder\DTOs\ListTravelOrdersInput;
use App\Application\TravelOrder\DTOs\ListTravelOrdersOutput;
use App\Domain\TravelOrder\Criteria\ListTravelOrdersCriteria;
use App\Domain\TravelOrder\Repositories\TravelOrderRepositoryInterface;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;

/**
 * Lista pedidos de viagem aplicando filtros e escopo por perfil (admin vê todos).
 */
final class ListTravelOrdersUseCase
{
    public function __construct(
        private readonly TravelOrderRepositoryInterface $orders,
        private readonly AuthenticatedUserPort $user,
    ) {}

    public function execute(ListTravelOrdersInput $input): ListTravelOrdersOutput
    {
        $criteria = new ListTravelOrdersCriteria(
            userId: $this->user->isAdmin() ? null : $this->user->userId(),
            status: $input->status !== null ? TravelOrderStatus::fromString($input->status) : null,
            destination: $input->destination,
            createdFrom: $input->createdFrom,
            createdTo: $input->createdTo,
            departureFrom: $input->departureFrom,
            departureTo: $input->departureTo,
        );

        return new ListTravelOrdersOutput($this->orders->list($criteria));
    }
}
