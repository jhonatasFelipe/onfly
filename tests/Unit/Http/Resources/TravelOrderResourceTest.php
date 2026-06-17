<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\TravelOrderResource;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use Illuminate\Http\Request;
use Tests\TestCase;
use Tests\Unit\Domain\TravelOrder\Support\MakesTravelOrder;

final class TravelOrderResourceTest extends TestCase
{
    use MakesTravelOrder;

    public function test_to_array_returns_expected_structure(): void
    {
        $order = $this->makeTravelOrder(
            requesterName: 'Alice',
            destination: 'Lisboa',
            departureDate: '2026-11-01',
            returnDate: '2026-11-10',
            status: TravelOrderStatus::Aprovado,
        );

        $resource = new TravelOrderResource($order);
        $array = $resource->toArray(Request::create('/'));

        $this->assertSame([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'requester_name' => 'Alice',
            'destination' => 'Lisboa',
            'departure_date' => '2026-11-01',
            'return_date' => '2026-11-10',
            'status' => 'aprovado',
        ], $array);
    }
}
