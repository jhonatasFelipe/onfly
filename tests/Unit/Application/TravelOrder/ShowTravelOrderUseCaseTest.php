<?php

declare(strict_types=1);

namespace Tests\Unit\Application\TravelOrder;

use App\Application\Ports\AuthenticatedUserPort;
use App\Application\TravelOrder\DTOs\ShowTravelOrderInput;
use App\Application\TravelOrder\UseCases\ShowTravelOrderUseCase;
use App\Domain\TravelOrder\Exceptions\TravelOrderNotFoundException;
use App\Domain\TravelOrder\Exceptions\UnauthorizedTravelOrderAccessException;
use App\Domain\TravelOrder\Repositories\TravelOrderRepositoryInterface;
use Mockery;
use Tests\Unit\Domain\TravelOrder\Support\MakesTravelOrder;
use Tests\Unit\UnitTestCase;

final class ShowTravelOrderUseCaseTest extends UnitTestCase
{
    use MakesTravelOrder;

    public function test_throws_when_order_not_found(): void
    {
        $orders = Mockery::mock(TravelOrderRepositoryInterface::class);
        $user = Mockery::mock(AuthenticatedUserPort::class);

        $orders->shouldReceive('findById')->once()->andReturn(null);
        $user->shouldNotReceive('isAdmin');

        $useCase = new ShowTravelOrderUseCase($orders, $user);

        $this->expectException(TravelOrderNotFoundException::class);
        $this->expectExceptionMessage('Travel order not found.');

        $useCase->execute(new ShowTravelOrderInput('550e8400-e29b-41d4-a716-446655440000'));
    }

    public function test_regular_user_cannot_view_other_users_order(): void
    {
        $order = $this->makeTravelOrder(userId: 1);
        $orders = Mockery::mock(TravelOrderRepositoryInterface::class);
        $user = Mockery::mock(AuthenticatedUserPort::class);

        $orders->shouldReceive('findById')->once()->andReturn($order);
        $user->shouldReceive('isAdmin')->once()->andReturn(false);
        $user->shouldReceive('userId')->once()->andReturn(99);

        $useCase = new ShowTravelOrderUseCase($orders, $user);

        $this->expectException(UnauthorizedTravelOrderAccessException::class);
        $this->expectExceptionMessage('You cannot view this travel order.');

        $useCase->execute(new ShowTravelOrderInput($order->id()));
    }

    public function test_owner_can_view_own_order(): void
    {
        $order = $this->makeTravelOrder(userId: 1);
        $orders = Mockery::mock(TravelOrderRepositoryInterface::class);
        $user = Mockery::mock(AuthenticatedUserPort::class);

        $orders->shouldReceive('findById')->once()->andReturn($order);
        $user->shouldReceive('isAdmin')->once()->andReturn(false);
        $user->shouldReceive('userId')->once()->andReturn(1);

        $useCase = new ShowTravelOrderUseCase($orders, $user);

        $output = $useCase->execute(new ShowTravelOrderInput($order->id()));

        $this->assertSame($order->id(), $output->order->id());
    }

    public function test_admin_can_view_any_order(): void
    {
        $order = $this->makeTravelOrder(userId: 1);
        $orders = Mockery::mock(TravelOrderRepositoryInterface::class);
        $user = Mockery::mock(AuthenticatedUserPort::class);

        $orders->shouldReceive('findById')->once()->andReturn($order);
        $user->shouldReceive('isAdmin')->once()->andReturn(true);
        $user->shouldNotReceive('userId');

        $useCase = new ShowTravelOrderUseCase($orders, $user);

        $output = $useCase->execute(new ShowTravelOrderInput($order->id()));

        $this->assertSame($order->id(), $output->order->id());
    }
}
