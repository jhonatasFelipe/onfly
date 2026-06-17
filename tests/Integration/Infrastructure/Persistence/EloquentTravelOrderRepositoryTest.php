<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Persistence;

use App\Domain\TravelOrder\Criteria\ListTravelOrdersCriteria;
use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\ValueObjects\Destination;
use App\Domain\TravelOrder\ValueObjects\RequesterName;
use App\Domain\TravelOrder\ValueObjects\TravelOrderId;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\TravelPeriod;
use App\Domain\TravelOrder\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Repositories\EloquentTravelOrderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentTravelOrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_and_find_returns_hydrated_entity(): void
    {
        $user = UserModel::factory()->create();
        $order = TravelOrder::create(
            userId: UserId::fromInt($user->id),
            requesterName: RequesterName::fromString($user->name),
            destination: Destination::fromString('Rio de Janeiro'),
            period: TravelPeriod::fromStrings('2026-10-01', '2026-10-15'),
        );

        $repository = app(EloquentTravelOrderRepository::class);
        $repository->save($order);

        $found = $repository->findById($order->id());

        $this->assertInstanceOf(TravelOrder::class, $found);
        $this->assertTrue($found->id()->equals($order->id()));
        $this->assertSame('Rio de Janeiro', $found->destination()->value());
    }

    public function test_find_by_id_returns_null_when_not_found(): void
    {
        $repository = app(EloquentTravelOrderRepository::class);

        $found = $repository->findById(
            TravelOrderId::fromString('550e8400-e29b-41d4-a716-446655440000'),
        );

        $this->assertNull($found);
    }

    public function test_save_updates_existing_order(): void
    {
        $user = UserModel::factory()->create();
        $order = TravelOrder::create(
            userId: UserId::fromInt($user->id),
            requesterName: RequesterName::fromString($user->name),
            destination: Destination::fromString('Brasilia'),
            period: TravelPeriod::fromStrings('2026-10-01', '2026-10-15'),
        );

        $repository = app(EloquentTravelOrderRepository::class);
        $repository->save($order);
        $order->approve();
        $repository->save($order);

        $found = $repository->findById($order->id());

        $this->assertSame(TravelOrderStatus::Aprovado, $found?->status());
    }

    public function test_list_returns_travel_order_collection(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->count(2)->create(['user_id' => $user->id]);

        $repository = app(EloquentTravelOrderRepository::class);
        $collection = $repository->list(new ListTravelOrdersCriteria(userId: UserId::fromInt($user->id)));

        $this->assertCount(2, $collection);

        foreach ($collection as $order) {
            $this->assertInstanceOf(TravelOrder::class, $order);
        }
    }

    public function test_list_applies_status_filter(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->create(['user_id' => $user->id, 'status' => 'solicitado']);
        TravelOrderModel::factory()->approved()->create(['user_id' => $user->id]);

        $repository = app(EloquentTravelOrderRepository::class);
        $collection = $repository->list(new ListTravelOrdersCriteria(
            userId: UserId::fromInt($user->id),
            status: TravelOrderStatus::Aprovado,
        ));

        $this->assertCount(1, $collection);
        $this->assertSame(TravelOrderStatus::Aprovado, $collection->all()[0]->status());
    }
}
