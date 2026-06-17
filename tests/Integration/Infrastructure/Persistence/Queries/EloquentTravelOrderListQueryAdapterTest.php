<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Persistence\Queries;

use App\Domain\TravelOrder\Criteria\ListTravelOrdersCriteria;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Queries\EloquentTravelOrderListQueryAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentTravelOrderListQueryAdapterTest extends TestCase
{
    use RefreshDatabase;

    private EloquentTravelOrderListQueryAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adapter = new EloquentTravelOrderListQueryAdapter();
    }

    public function test_filters_by_user_id(): void
    {
        $owner = UserModel::factory()->create();
        $other = UserModel::factory()->create();
        TravelOrderModel::factory()->create(['user_id' => $owner->id]);
        TravelOrderModel::factory()->create(['user_id' => $other->id]);

        $results = $this->adapter
            ->apply(TravelOrderModel::query(), new ListTravelOrdersCriteria(userId: UserId::fromInt($owner->id)))
            ->get();

        $this->assertCount(1, $results);
        $this->assertSame($owner->id, $results->first()->user_id);
    }

    public function test_filters_by_status(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->create(['user_id' => $user->id, 'status' => 'solicitado']);
        TravelOrderModel::factory()->approved()->create(['user_id' => $user->id]);

        $results = $this->adapter
            ->apply(TravelOrderModel::query(), new ListTravelOrdersCriteria(
                status: TravelOrderStatus::Aprovado,
            ))
            ->get();

        $this->assertCount(1, $results);
        $this->assertSame('aprovado', $results->first()->status);
    }

    public function test_filters_by_destination(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->create(['user_id' => $user->id, 'destination' => 'Salvador']);
        TravelOrderModel::factory()->create(['user_id' => $user->id, 'destination' => 'Curitiba']);

        $results = $this->adapter
            ->apply(TravelOrderModel::query(), new ListTravelOrdersCriteria(destination: 'Salv'))
            ->get();

        $this->assertCount(1, $results);
        $this->assertSame('Salvador', $results->first()->destination);
    }

    public function test_ignores_empty_destination_filter(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->count(2)->create(['user_id' => $user->id]);

        $results = $this->adapter
            ->apply(TravelOrderModel::query(), new ListTravelOrdersCriteria(destination: ''))
            ->get();

        $this->assertCount(2, $results);
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

        $results = $this->adapter
            ->apply(TravelOrderModel::query(), new ListTravelOrdersCriteria(
                createdFrom: '2026-05-01',
                createdTo: '2026-07-01',
            ))
            ->get();

        $this->assertCount(1, $results);
    }

    public function test_filters_by_departure_date_range(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->create([
            'user_id' => $user->id,
            'departure_date' => '2026-04-01',
        ]);
        TravelOrderModel::factory()->create([
            'user_id' => $user->id,
            'departure_date' => '2026-08-01',
        ]);

        $results = $this->adapter
            ->apply(TravelOrderModel::query(), new ListTravelOrdersCriteria(
                departureFrom: '2026-07-01',
                departureTo: '2026-09-01',
            ))
            ->get();

        $this->assertCount(1, $results);
        $this->assertSame('2026-08-01', $results->first()->departure_date->format('Y-m-d'));
    }

    public function test_orders_by_created_at_descending(): void
    {
        $user = UserModel::factory()->create();
        $older = TravelOrderModel::factory()->create([
            'user_id' => $user->id,
            'created_at' => '2026-01-01 10:00:00',
        ]);
        $newer = TravelOrderModel::factory()->create([
            'user_id' => $user->id,
            'created_at' => '2026-06-01 10:00:00',
        ]);

        $results = $this->adapter
            ->apply(TravelOrderModel::query(), new ListTravelOrdersCriteria())
            ->get();

        $this->assertTrue($results->first()->is($newer));
        $this->assertTrue($results->last()->is($older));
    }
}
