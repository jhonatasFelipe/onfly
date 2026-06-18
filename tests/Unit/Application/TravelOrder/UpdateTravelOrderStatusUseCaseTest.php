<?php

declare(strict_types=1);

namespace Tests\Unit\Application\TravelOrder;

use App\Application\Ports\EventDispatcherPort;
use App\Application\TravelOrder\DTOs\UpdateTravelOrderStatusInput;
use App\Application\TravelOrder\UseCases\UpdateTravelOrderStatusUseCase;
use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\Events\TravelOrderApproved;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;
use App\Domain\TravelOrder\Exceptions\InvalidTravelOrderStateException;
use App\Domain\TravelOrder\Exceptions\TravelOrderNotFoundException;
use App\Domain\TravelOrder\Repositories\TravelOrderRepositoryInterface;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use Mockery;
use Tests\Unit\Domain\TravelOrder\Support\MakesTravelOrder;
use Tests\Unit\UnitTestCase;

final class UpdateTravelOrderStatusUseCaseTest extends UnitTestCase
{
    use MakesTravelOrder;

    public function test_throws_when_order_not_found(): void
    {
        $orders = Mockery::mock(TravelOrderRepositoryInterface::class);
        $events = Mockery::mock(EventDispatcherPort::class);

        $orders->shouldReceive('findById')
            ->once()
            ->with('550e8400-e29b-41d4-a716-446655440000')
            ->andReturn(null);
        $orders->shouldNotReceive('save');
        $events->shouldNotReceive('dispatch');

        $useCase = new UpdateTravelOrderStatusUseCase($orders, $events);

        $this->expectException(TravelOrderNotFoundException::class);

        $useCase->execute(new UpdateTravelOrderStatusInput(
            orderId: '550e8400-e29b-41d4-a716-446655440000',
            status: 'aprovado',
        ));
    }

    public function test_cannot_revert_to_solicitado(): void
    {
        $order = $this->makeTravelOrder();
        $orders = Mockery::mock(TravelOrderRepositoryInterface::class);
        $events = Mockery::mock(EventDispatcherPort::class);

        $orders->shouldReceive('findById')
            ->once()
            ->with($order->id())
            ->andReturn($order);
        $orders->shouldNotReceive('save');
        $events->shouldNotReceive('dispatch');

        $useCase = new UpdateTravelOrderStatusUseCase($orders, $events);

        $this->expectException(InvalidTravelOrderStateException::class);
        $this->expectExceptionMessage('Cannot revert to requested status.');

        $useCase->execute(new UpdateTravelOrderStatusInput(
            orderId: $order->id(),
            status: 'solicitado',
        ));
    }

    public function test_approves_order_and_dispatches_event(): void
    {
        $order = $this->makeTravelOrder();
        $orders = Mockery::mock(TravelOrderRepositoryInterface::class);
        $events = Mockery::mock(EventDispatcherPort::class);

        $orders->shouldReceive('findById')
            ->once()
            ->with($order->id())
            ->andReturn($order);
        $orders->shouldReceive('save')
            ->once()
            ->with(Mockery::on(fn (TravelOrder $saved) => $saved->status() === TravelOrderStatus::Aprovado));
        $events->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(TravelOrderApproved::class));

        $useCase = new UpdateTravelOrderStatusUseCase($orders, $events);

        $output = $useCase->execute(new UpdateTravelOrderStatusInput(
            orderId: $order->id(),
            status: 'aprovado',
        ));

        $this->assertSame(TravelOrderStatus::Aprovado, $output->order->status());
    }

    public function test_cancels_order_and_dispatches_event(): void
    {
        $order = $this->makeTravelOrder();
        $orders = Mockery::mock(TravelOrderRepositoryInterface::class);
        $events = Mockery::mock(EventDispatcherPort::class);

        $orders->shouldReceive('findById')
            ->once()
            ->with($order->id())
            ->andReturn($order);
        $orders->shouldReceive('save')
            ->once()
            ->with(Mockery::on(fn (TravelOrder $saved) => $saved->status() === TravelOrderStatus::Cancelado));
        $events->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(TravelOrderCancelled::class));

        $useCase = new UpdateTravelOrderStatusUseCase($orders, $events);

        $output = $useCase->execute(new UpdateTravelOrderStatusInput(
            orderId: $order->id(),
            status: 'cancelado',
        ));

        $this->assertSame(TravelOrderStatus::Cancelado, $output->order->status());
    }

    public function test_propagates_invalid_state_when_approving_non_solicitado_order(): void
    {
        $order = $this->makeTravelOrder(status: TravelOrderStatus::Aprovado);
        $orders = Mockery::mock(TravelOrderRepositoryInterface::class);
        $events = Mockery::mock(EventDispatcherPort::class);

        $orders->shouldReceive('findById')
            ->once()
            ->with($order->id())
            ->andReturn($order);
        $orders->shouldNotReceive('save');
        $events->shouldNotReceive('dispatch');

        $useCase = new UpdateTravelOrderStatusUseCase($orders, $events);

        $this->expectException(InvalidTravelOrderStateException::class);

        $useCase->execute(new UpdateTravelOrderStatusInput(
            orderId: $order->id(),
            status: 'aprovado',
        ));
    }
}
