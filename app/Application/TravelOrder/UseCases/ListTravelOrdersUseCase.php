<?php

declare(strict_types=1);

namespace App\Application\TravelOrder\UseCases;

use App\Application\Ports\AuthenticatedUserPort;
use App\Application\Ports\TravelOrderListQueryPort;
use App\Application\TravelOrder\DTOs\ListTravelOrdersInput;
use App\Application\TravelOrder\DTOs\ListTravelOrdersOutput;
use App\Domain\Shared\ValueObjects\Pagination;
use App\Domain\TravelOrder\Criteria\ListTravelOrdersCriteria;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;

/**
 * Lista pedidos de viagem aplicando filtros, paginação e escopo por perfil (admin vê todos).
 */
final class ListTravelOrdersUseCase
{
    public function __construct(
        private readonly TravelOrderListQueryPort $listQuery,
        private readonly AuthenticatedUserPort $user,
    ) {}

    public function execute(ListTravelOrdersInput $input): ListTravelOrdersOutput
    {
        $criteria = new ListTravelOrdersCriteria(
            pagination: new Pagination($input->page, $input->perPage),
            userId: $this->user->isAdmin() ? null : $this->user->userId(),
            status: $input->status !== null ? TravelOrderStatus::fromString($input->status) : null,
            destination: $input->destination,
            createdFrom: $input->createdFrom,
            createdTo: $input->createdTo,
            departureFrom: $input->departureFrom,
            departureTo: $input->departureTo,
        );

        return new ListTravelOrdersOutput($this->listQuery->paginate($criteria));
    }
}
