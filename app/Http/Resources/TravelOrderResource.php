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
            /** @format uuid */
            'id' => $order->id(),
            'requester_name' => $order->requesterName(),
            'destination' => $order->destination(),
            /** @format date */
            'departure_date' => $order->period()->departure->format('Y-m-d'),
            /** @format date */
            'return_date' => $order->period()->return->format('Y-m-d'),
            /** @var 'solicitado'|'aprovado'|'cancelado' */
            'status' => $order->status()->value,
        ];
    }
}
