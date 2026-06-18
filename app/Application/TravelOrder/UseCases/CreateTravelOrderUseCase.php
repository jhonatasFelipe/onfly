<?php

declare(strict_types=1);

namespace App\Application\TravelOrder\UseCases;

use App\Application\Ports\AuthenticatedUserPort;
use App\Application\TravelOrder\DTOs\CreateTravelOrderInput;
use App\Application\TravelOrder\DTOs\CreateTravelOrderOutput;
use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\Repositories\TravelOrderRepositoryInterface;
use App\Domain\TravelOrder\ValueObjects\TravelPeriod;

/**
 * Cria um novo pedido de viagem para o usuário autenticado.
 */
final class CreateTravelOrderUseCase
{
    public function __construct(
        private readonly TravelOrderRepositoryInterface $orders,
        private readonly AuthenticatedUserPort $user,
    ) {}

    public function execute(CreateTravelOrderInput $input): CreateTravelOrderOutput
    {
        $order = TravelOrder::create(
            userId: $this->user->userId(),
            requesterName: $this->user->requesterName(),
            destination: $input->destination,
            period: TravelPeriod::fromStrings($input->departureDate, $input->returnDate),
        );

        $this->orders->save($order);

        return new CreateTravelOrderOutput($order);
    }
}
