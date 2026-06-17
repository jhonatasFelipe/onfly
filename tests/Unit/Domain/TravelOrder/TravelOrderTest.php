<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TravelOrder;

use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\Events\TravelOrderApproved;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;
use App\Domain\TravelOrder\Exceptions\InvalidTravelOrderStateException;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Domain\TravelOrder\Support\MakesTravelOrder;

final class TravelOrderTest extends TestCase
{
    use MakesTravelOrder;

    public function test_create_generates_uuid_id_and_solicitado_status(): void
    {
        $order = $this->createTravelOrder();

        $this->assertTrue($order->status()->isSolicitado());
        $this->assertNotEmpty($order->id()->value());
    }

    public function test_reconstitute_preserves_given_state(): void
    {
        $order = $this->makeTravelOrder(status: TravelOrderStatus::Aprovado);

        $this->assertSame(TravelOrderStatus::Aprovado, $order->status());
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $order->id()->value());
    }

    public function test_getters_return_constructed_values(): void
    {
        $order = $this->makeTravelOrder(userId: 5, requesterName: 'Alice', destination: 'Lisboa');

        $this->assertSame(5, $order->userId()->value());
        $this->assertSame('Alice', $order->requesterName()->value());
        $this->assertSame('Lisboa', $order->destination()->value());
        $this->assertSame('2026-08-01', $order->period()->departure->format('Y-m-d'));
        $this->assertSame('2026-08-10', $order->period()->return->format('Y-m-d'));
    }

    public function test_belongs_to_returns_true_for_owner(): void
    {
        $order = $this->makeTravelOrder(userId: 7);

        $this->assertTrue($order->belongsTo(UserId::fromInt(7)));
    }

    public function test_belongs_to_returns_false_for_other_user(): void
    {
        $order = $this->makeTravelOrder(userId: 7);

        $this->assertFalse($order->belongsTo(UserId::fromInt(99)));
    }

    public function test_approve_changes_status_when_solicitado(): void
    {
        $order = $this->createTravelOrder();

        $order->approve();

        $this->assertSame(TravelOrderStatus::Aprovado, $order->status());
        $this->assertInstanceOf(TravelOrderApproved::class, $order->pullDomainEvents()[0]);
    }

    public function test_cannot_approve_when_not_solicitado(): void
    {
        $order = $this->makeTravelOrder(status: TravelOrderStatus::Aprovado);

        $this->expectException(InvalidTravelOrderStateException::class);
        $this->expectExceptionMessage('Only requested orders can be approved.');

        $order->approve();
    }

    public function test_cancel_changes_status_when_solicitado(): void
    {
        $order = $this->createTravelOrder();

        $order->cancel();

        $this->assertSame(TravelOrderStatus::Cancelado, $order->status());
        $this->assertInstanceOf(TravelOrderCancelled::class, $order->pullDomainEvents()[0]);
    }

    public function test_cannot_cancel_when_aprovado(): void
    {
        $order = $this->makeTravelOrder(status: TravelOrderStatus::Aprovado);

        $this->expectException(InvalidTravelOrderStateException::class);
        $this->expectExceptionMessage('Only requested orders can be cancelled.');

        $order->cancel();
    }

    public function test_pull_domain_events_clears_events_after_pull(): void
    {
        $order = $this->createTravelOrder();
        $order->approve();

        $events = $order->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertSame([], $order->pullDomainEvents());
    }
}
