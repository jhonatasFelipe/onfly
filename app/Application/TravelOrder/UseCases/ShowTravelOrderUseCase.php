<?php

declare(strict_types=1);

namespace App\Application\TravelOrder\UseCases;

use App\Application\Ports\AuthenticatedUserPort;
use App\Application\TravelOrder\DTOs\ShowTravelOrderInput;
use App\Application\TravelOrder\DTOs\ShowTravelOrderOutput;
use App\Domain\TravelOrder\Exceptions\TravelOrderNotFoundException;
use App\Domain\TravelOrder\Exceptions\UnauthorizedTravelOrderAccessException;
use App\Domain\TravelOrder\Repositories\TravelOrderRepositoryInterface;
use App\Domain\TravelOrder\ValueObjects\TravelOrderId;

/**
 * Recupera um pedido de viagem, respeitando ownership (usuário) ou perfil admin.
 */
final class ShowTravelOrderUseCase
{
    public function __construct(
        private readonly TravelOrderRepositoryInterface $orders,
        private readonly AuthenticatedUserPort $user,
    ) {}

    /**
     * @throws TravelOrderNotFoundException
     * @throws UnauthorizedTravelOrderAccessException
     */
    public function execute(ShowTravelOrderInput $input): ShowTravelOrderOutput
    {
        $order = $this->orders->findById(TravelOrderId::fromString($input->orderId))
            ?? throw new TravelOrderNotFoundException('Travel order not found.');

        if (! $this->user->isAdmin() && ! $order->belongsTo($this->user->userId())) {
            throw new UnauthorizedTravelOrderAccessException('You cannot view this travel order.');
        }

        return new ShowTravelOrderOutput($order);
    }
}
