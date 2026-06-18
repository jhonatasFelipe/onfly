<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Persistence\Queries;

use App\Application\Ports\TravelOrderListQueryPort;
use App\Domain\Shared\ValueObjects\Pagination;
use App\Domain\TravelOrder\Criteria\ListTravelOrdersCriteria;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentTravelOrderListQueryAdapterTest extends TestCase
{
    use RefreshDatabase;

    private TravelOrderListQueryPort $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = app(TravelOrderListQueryPort::class);
    }

    public function test_filters_by_user_id(): void
    {
        $owner = UserModel::factory()->create();
        $other = UserModel::factory()->create();
        TravelOrderModel::factory()->create(['user_id' => $owner->id]);
        TravelOrderModel::factory()->create(['user_id' => $other->id]);

        $page = $this->query->paginate(new ListTravelOrdersCriteria(
            pagination: new Pagination(1, 15),
            userId: $owner->id,
        ));

        $this->assertSame(1, $page->total);
        $this->assertSame($owner->id, $page->items->all()[0]->userId());
    }

    public function test_filters_by_status(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->create(['user_id' => $user->id, 'status' => 'solicitado']);
        TravelOrderModel::factory()->approved()->create(['user_id' => $user->id]);

        $page = $this->query->paginate(new ListTravelOrdersCriteria(
            pagination: new Pagination(1, 15),
            status: TravelOrderStatus::Aprovado,
        ));

        $this->assertSame(1, $page->total);
        $this->assertSame(TravelOrderStatus::Aprovado, $page->items->all()[0]->status());
    }

    public function test_filters_by_destination(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->create(['user_id' => $user->id, 'destination' => 'Salvador']);
        TravelOrderModel::factory()->create(['user_id' => $user->id, 'destination' => 'Curitiba']);

        $page = $this->query->paginate(new ListTravelOrdersCriteria(
            pagination: new Pagination(1, 15),
            destination: 'Salv',
        ));

        $this->assertSame(1, $page->total);
        $this->assertSame('Salvador', $page->items->all()[0]->destination());
    }

    public function test_ignores_empty_destination_filter(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->count(2)->create(['user_id' => $user->id]);

        $page = $this->query->paginate(new ListTravelOrdersCriteria(
            pagination: new Pagination(1, 15),
            destination: '',
        ));

        $this->assertSame(2, $page->total);
    }

    public function test_filters_by_created_date_range(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->create([
            'user_id' => $user->id,
            'created_at' => '2026-03-01 10:00:00',
        ]);
        TravelOrderModel::factory()->create([
            'user_id' => $user->id,
            'created_at' => '2026-06-01 10:00:00',
        ]);

        $page = $this->query->paginate(new ListTravelOrdersCriteria(
            pagination: new Pagination(1, 15),
            createdFrom: '2026-05-01',
            createdTo: '2026-07-01',
        ));

        $this->assertSame(1, $page->total);
    }

    public function test_filters_by_departure_date_range(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->create([
            'user_id' => $user->id,
            'departure_date' => '2026-04-01',
            'return_date' => '2026-04-10',
        ]);
        TravelOrderModel::factory()->create([
            'user_id' => $user->id,
            'departure_date' => '2026-08-01',
            'return_date' => '2026-08-10',
        ]);

        $page = $this->query->paginate(new ListTravelOrdersCriteria(
            pagination: new Pagination(1, 15),
            departureFrom: '2026-07-01',
            departureTo: '2026-09-01',
        ));

        $this->assertSame(1, $page->total);
        $this->assertSame('2026-08-01', $page->items->all()[0]->period()->departure->format('Y-m-d'));
    }

    public function test_orders_by_created_at_descending(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->create([
            'user_id' => $user->id,
            'destination' => 'Older Trip',
            'created_at' => '2026-01-01 10:00:00',
        ]);
        TravelOrderModel::factory()->create([
            'user_id' => $user->id,
            'destination' => 'Newer Trip',
            'created_at' => '2026-06-01 10:00:00',
        ]);

        $page = $this->query->paginate(new ListTravelOrdersCriteria(
            pagination: new Pagination(1, 15),
            userId: $user->id,
        ));

        $this->assertSame('Newer Trip', $page->items->all()[0]->destination());
        $this->assertSame('Older Trip', $page->items->all()[1]->destination());
    }

    public function test_paginates_results(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->count(5)->create(['user_id' => $user->id]);

        $page = $this->query->paginate(new ListTravelOrdersCriteria(
            pagination: new Pagination(2, 2),
            userId: $user->id,
        ));

        $this->assertSame(5, $page->total);
        $this->assertCount(2, $page->items);
        $this->assertSame(2, $page->pagination->page);
        $this->assertSame(2, $page->pagination->perPage);
        $this->assertSame(3, $page->lastPage());
    }
}
