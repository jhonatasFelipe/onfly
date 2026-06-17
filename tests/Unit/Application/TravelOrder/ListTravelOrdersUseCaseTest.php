<?php

declare(strict_types=1);

namespace Tests\Unit\Application\TravelOrder;

use App\Application\Ports\AuthenticatedUserPort;
use App\Application\TravelOrder\DTOs\ListTravelOrdersInput;
use App\Application\TravelOrder\UseCases\ListTravelOrdersUseCase;
use App\Domain\TravelOrder\Collections\TravelOrderCollection;
use App\Domain\TravelOrder\Criteria\ListTravelOrdersCriteria;
use App\Domain\TravelOrder\Repositories\TravelOrderRepositoryInterface;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\UserId;
use Mockery;
use Tests\Unit\Domain\TravelOrder\Support\MakesTravelOrder;
use Tests\Unit\UnitTestCase;

final class ListTravelOrdersUseCaseTest extends UnitTestCase
{
    use MakesTravelOrder;

    public function test_admin_lists_all_orders_without_user_filter(): void
    {
        $orders = Mockery::mock(TravelOrderRepositoryInterface::class);
        $user = Mockery::mock(AuthenticatedUserPort::class);
        $collection = TravelOrderCollection::fromArray($this->createTravelOrder());

        $user->shouldReceive('isAdmin')->once()->andReturn(true);
        $orders->shouldReceive('list')
            ->once()
            ->with(Mockery::on(fn (ListTravelOrdersCriteria $criteria) => $criteria->userId === null
                && $criteria->status === null))
            ->andReturn($collection);

        $useCase = new ListTravelOrdersUseCase($orders, $user);

        $output = $useCase->execute(new ListTravelOrdersInput(
            status: null,
            destination: null,
            createdFrom: null,
            createdTo: null,
            departureFrom: null,
            departureTo: null,
        ));

        $this->assertCount(1, $output->orders);
    }

    public function test_regular_user_lists_only_own_orders(): void
    {
        $orders = Mockery::mock(TravelOrderRepositoryInterface::class);
        $user = Mockery::mock(AuthenticatedUserPort::class);
        $collection = TravelOrderCollection::empty();

        $user->shouldReceive('isAdmin')->once()->andReturn(false);
        $user->shouldReceive('userId')->once()->andReturn(UserId::fromInt(5));
        $orders->shouldReceive('list')
            ->once()
            ->with(Mockery::on(fn (ListTravelOrdersCriteria $criteria) => $criteria->userId?->value() === 5))
            ->andReturn($collection);

        $useCase = new ListTravelOrdersUseCase($orders, $user);

        $output = $useCase->execute(new ListTravelOrdersInput(
            status: null,
            destination: null,
            createdFrom: null,
            createdTo: null,
            departureFrom: null,
            departureTo: null,
        ));

        $this->assertCount(0, $output->orders);
    }

    public function test_passes_status_filter_to_repository(): void
    {
        $orders = Mockery::mock(TravelOrderRepositoryInterface::class);
        $user = Mockery::mock(AuthenticatedUserPort::class);

        $user->shouldReceive('isAdmin')->once()->andReturn(true);
        $orders->shouldReceive('list')
            ->once()
            ->with(Mockery::on(fn (ListTravelOrdersCriteria $criteria) => $criteria->status === TravelOrderStatus::Aprovado
                && $criteria->destination === 'Paris'))
            ->andReturn(TravelOrderCollection::empty());

        $useCase = new ListTravelOrdersUseCase($orders, $user);

        $output = $useCase->execute(new ListTravelOrdersInput(
            status: 'aprovado',
            destination: 'Paris',
            createdFrom: '2026-01-01',
            createdTo: '2026-12-31',
            departureFrom: '2026-06-01',
            departureTo: '2026-06-30',
        ));

        $this->assertCount(0, $output->orders);
    }
}
