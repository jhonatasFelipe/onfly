<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\TravelOrder\Entities\TravelOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TravelOrder */
final class TravelOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var TravelOrder $order */
        $order = $this->resource;

        return [
            'id' => $order->id()->value(),
            'requester_name' => $order->requesterName()->value(),
            'destination' => $order->destination()->value(),
            'departure_date' => $order->period()->departure->format('Y-m-d'),
            'return_date' => $order->period()->return->format('Y-m-d'),
            'status' => $order->status()->value,
        ];
    }
}
