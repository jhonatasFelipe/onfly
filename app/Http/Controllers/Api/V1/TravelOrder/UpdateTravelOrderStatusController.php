<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\TravelOrder;

use App\Application\TravelOrder\DTOs\UpdateTravelOrderStatusInput;
use App\Application\TravelOrder\UseCases\UpdateTravelOrderStatusUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\TravelOrder\UpdateTravelOrderStatusRequest;
use App\Http\Resources\TravelOrderResource;
use Illuminate\Http\JsonResponse;

/**
 * Atualiza status de pedido de viagem via API — delega ao UpdateTravelOrderStatusUseCase.
 */
final class UpdateTravelOrderStatusController extends Controller
{
    public function __invoke(
        string $id,
        UpdateTravelOrderStatusRequest $request,
        UpdateTravelOrderStatusUseCase $useCase,
    ): JsonResponse {
        $output = $useCase->execute(new UpdateTravelOrderStatusInput(
            orderId: $id,
            status: $request->validated('status'),
        ));

        return (new TravelOrderResource($output->order))->response();
    }
}
