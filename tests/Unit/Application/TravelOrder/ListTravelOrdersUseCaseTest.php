<?php

declare(strict_types=1);

namespace Tests\Unit\Application\TravelOrder;

use App\Application\Ports\AuthenticatedUserPort;
use App\Application\Ports\TravelOrderListQueryPort;
use App\Application\TravelOrder\DTOs\ListTravelOrdersInput;
use App\Application\TravelOrder\UseCases\ListTravelOrdersUseCase;
use App\Domain\Shared\ValueObjects\Pagination;
use App\Domain\TravelOrder\Collections\PaginatedTravelOrders;
use App\Domain\TravelOrder\Collections\TravelOrderCollection;
use App\Domain\TravelOrder\Criteria\ListTravelOrdersCriteria;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use Mockery;
use Tests\Unit\Domain\TravelOrder\Support\MakesTravelOrder;
use Tests\Unit\UnitTestCase;

final class ListTravelOrdersUseCaseTest extends UnitTestCase
{
    use MakesTravelOrder;

    public function test_admin_lists_all_orders_without_user_filter(): void
    {
        $listQuery = Mockery::mock(TravelOrderListQueryPort::class);
        $user = Mockery::mock(AuthenticatedUserPort::class);
        $page = new PaginatedTravelOrders(
            items: TravelOrderCollection::fromArray($this->createTravelOrder()),
            total: 1,
            pagination: new Pagination(1, 15),
        );

        $user->shouldReceive('isAdmin')->once()->andReturn(true);
        $listQuery->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(fn (ListTravelOrdersCriteria $criteria) => $criteria->userId === null
                && $criteria->status === null
                && $criteria->pagination->page === 1
                && $criteria->pagination->perPage === 15))
            ->andReturn($page);

        $useCase = new ListTravelOrdersUseCase($listQuery, $user);

        $output = $useCase->execute(new ListTravelOrdersInput(
            page: 1,
            perPage: 15,
            status: null,
            destination: null,
            createdFrom: null,
            createdTo: null,
            departureFrom: null,
            departureTo: null,
        ));

        $this->assertCount(1, $output->page->items);
    }

    public function test_regular_user_lists_only_own_orders(): void
    {
        $listQuery = Mockery::mock(TravelOrderListQueryPort::class);
        $user = Mockery::mock(AuthenticatedUserPort::class);
        $page = new PaginatedTravelOrders(
            items: TravelOrderCollection::empty(),
            total: 0,
            pagination: new Pagination(1, 15),
        );

        $user->shouldReceive('isAdmin')->once()->andReturn(false);
        $user->shouldReceive('userId')->once()->andReturn(5);
        $listQuery->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(fn (ListTravelOrdersCriteria $criteria) => $criteria->userId === 5))
            ->andReturn($page);

        $useCase = new ListTravelOrdersUseCase($listQuery, $user);

        $output = $useCase->execute(new ListTravelOrdersInput(
            page: 1,
            perPage: 15,
            status: null,
            destination: null,
            createdFrom: null,
            createdTo: null,
            departureFrom: null,
            departureTo: null,
        ));

        $this->assertCount(0, $output->page->items);
    }

    public function test_passes_status_filter_to_query_port(): void
    {
        $listQuery = Mockery::mock(TravelOrderListQueryPort::class);
        $user = Mockery::mock(AuthenticatedUserPort::class);

        $user->shouldReceive('isAdmin')->once()->andReturn(true);
        $listQuery->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(fn (ListTravelOrdersCriteria $criteria) => $criteria->status === TravelOrderStatus::Aprovado
                && $criteria->destination === 'Paris'))
            ->andReturn(new PaginatedTravelOrders(
                items: TravelOrderCollection::empty(),
                total: 0,
                pagination: new Pagination(2, 10),
            ));

        $useCase = new ListTravelOrdersUseCase($listQuery, $user);

        $output = $useCase->execute(new ListTravelOrdersInput(
            page: 2,
            perPage: 10,
            status: 'aprovado',
            destination: 'Paris',
            createdFrom: '2026-01-01',
            createdTo: '2026-12-31',
            departureFrom: '2026-06-01',
            departureTo: '2026-06-30',
        ));

        $this->assertCount(0, $output->page->items);
    }
}
