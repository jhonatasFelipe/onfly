<?php

declare(strict_types=1);

namespace Tests\Unit\Application\TravelOrder;

use App\Application\Ports\AuthenticatedUserPort;
use App\Application\TravelOrder\DTOs\CreateTravelOrderInput;
use App\Application\TravelOrder\UseCases\CreateTravelOrderUseCase;
use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\Exceptions\InvalidTravelPeriodException;
use App\Domain\TravelOrder\Repositories\TravelOrderRepositoryInterface;
use Mockery;
use Tests\Unit\UnitTestCase;

final class CreateTravelOrderUseCaseTest extends UnitTestCase
{
    public function test_saves_order_with_generated_id(): void
    {
        $repository = Mockery::mock(TravelOrderRepositoryInterface::class);
        $user = Mockery::mock(AuthenticatedUserPort::class);

        $user->shouldReceive('userId')->once()->andReturn(1);
        $user->shouldReceive('requesterName')->once()->andReturn('Jane');

        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::on(fn (TravelOrder $order) => $order->id() !== ''
                && $order->destination() === 'Tokyo'));

        $useCase = new CreateTravelOrderUseCase($repository, $user);

        $output = $useCase->execute(new CreateTravelOrderInput(
            destination: 'Tokyo',
            departureDate: '2026-09-01',
            returnDate: '2026-09-10',
        ));

        $this->assertSame('Tokyo', $output->order->destination());
    }

    public function test_propagates_invalid_travel_period_exception(): void
    {
        $repository = Mockery::mock(TravelOrderRepositoryInterface::class);
        $user = Mockery::mock(AuthenticatedUserPort::class);

        $user->shouldReceive('userId')->once()->andReturn(1);
        $user->shouldReceive('requesterName')->once()->andReturn('Jane');
        $repository->shouldNotReceive('save');

        $useCase = new CreateTravelOrderUseCase($repository, $user);

        $this->expectException(InvalidTravelPeriodException::class);

        $useCase->execute(new CreateTravelOrderInput(
            destination: 'Tokyo',
            departureDate: '2026-09-10',
            returnDate: '2026-09-01',
        ));
    }
}
