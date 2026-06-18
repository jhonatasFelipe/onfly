<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TravelOrder;

use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\Events\TravelOrderApproved;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;
use App\Domain\TravelOrder\Exceptions\InvalidTravelOrderStateException;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\TravelPeriod;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Domain\TravelOrder\Support\MakesTravelOrder;

final class TravelOrderTest extends TestCase
{
    use MakesTravelOrder;

    public function test_create_generates_uuid_id_and_solicitado_status(): void
    {
        $order = $this->createTravelOrder();

        $this->assertTrue($order->status()->isSolicitado());
        $this->assertNotEmpty($order->id());
    }

    public function test_reconstitute_preserves_given_state(): void
    {
        $order = $this->makeTravelOrder(status: TravelOrderStatus::Aprovado);

        $this->assertSame(TravelOrderStatus::Aprovado, $order->status());
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $order->id());
    }

    public function test_getters_return_constructed_values(): void
    {
        $order = $this->makeTravelOrder(userId: 5, requesterName: 'Alice', destination: 'Lisboa');

        $this->assertSame(5, $order->userId());
        $this->assertSame('Alice', $order->requesterName());
        $this->assertSame('Lisboa', $order->destination());
        $this->assertSame('2026-08-01', $order->period()->departure->format('Y-m-d'));
        $this->assertSame('2026-08-10', $order->period()->return->format('Y-m-d'));
    }

    public function test_create_trims_destination_and_requester_name(): void
    {
        $order = TravelOrder::create(
            userId: 1,
            requesterName: '  Jane Doe  ',
            destination: '  Tokyo  ',
            period: TravelPeriod::fromStrings('2026-08-01', '2026-08-10'),
        );

        $this->assertSame('Jane Doe', $order->requesterName());
        $this->assertSame('Tokyo', $order->destination());
    }

    public function test_rejects_empty_destination(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Destination cannot be empty.');

        $this->makeTravelOrder(destination: '   ');
    }

    public function test_rejects_destination_exceeding_255_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Destination cannot exceed 255 characters.');

        $this->makeTravelOrder(destination: str_repeat('a', 256));
    }

    public function test_rejects_empty_requester_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Requester name cannot be empty.');

        $this->makeTravelOrder(requesterName: '');
    }

    public function test_rejects_requester_name_exceeding_255_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Requester name cannot exceed 255 characters.');

        $this->makeTravelOrder(requesterName: str_repeat('x', 256));
    }

    public function test_rejects_invalid_order_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID: not-a-uuid');

        $this->makeTravelOrder(id: 'not-a-uuid');
    }

    public function test_rejects_non_positive_user_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID must be greater than zero.');

        $this->makeTravelOrder(userId: 0);
    }

    public function test_belongs_to_returns_true_for_owner(): void
    {
        $order = $this->makeTravelOrder(userId: 7);

        $this->assertTrue($order->belongsTo(7));
    }

    public function test_belongs_to_returns_false_for_other_user(): void
    {
        $order = $this->makeTravelOrder(userId: 7);

        $this->assertFalse($order->belongsTo(99));
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
