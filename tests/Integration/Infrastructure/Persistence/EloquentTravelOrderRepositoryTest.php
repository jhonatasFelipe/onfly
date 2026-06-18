<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Persistence;

use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\TravelPeriod;
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
            userId: $user->id,
            requesterName: $user->name,
            destination: 'Rio de Janeiro',
            period: TravelPeriod::fromStrings('2026-10-01', '2026-10-15'),
        );

        $repository = app(EloquentTravelOrderRepository::class);
        $repository->save($order);

        $found = $repository->findById($order->id());

        $this->assertInstanceOf(TravelOrder::class, $found);
        $this->assertSame($order->id(), $found->id());
        $this->assertSame('Rio de Janeiro', $found->destination());
    }

    public function test_find_by_id_returns_null_when_not_found(): void
    {
        $repository = app(EloquentTravelOrderRepository::class);

        $found = $repository->findById('550e8400-e29b-41d4-a716-446655440000');

        $this->assertNull($found);
    }

    public function test_save_updates_existing_order(): void
    {
        $user = UserModel::factory()->create();
        $order = TravelOrder::create(
            userId: $user->id,
            requesterName: $user->name,
            destination: 'Brasilia',
            period: TravelPeriod::fromStrings('2026-10-01', '2026-10-15'),
        );

        $repository = app(EloquentTravelOrderRepository::class);
        $repository->save($order);
        $order->approve();
        $repository->save($order);

        $found = $repository->findById($order->id());

        $this->assertSame(TravelOrderStatus::Aprovado, $found?->status());
    }
}
