<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\TravelOrder;

use App\Application\TravelOrder\DTOs\CreateTravelOrderInput;
use App\Application\TravelOrder\UseCases\CreateTravelOrderUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\TravelOrder\StoreTravelOrderRequest;
use App\Http\Resources\TravelOrderResource;
use Illuminate\Http\JsonResponse;

/**
 * Cria pedido de viagem via API — delega ao CreateTravelOrderUseCase.
 */
final class StoreTravelOrderController extends Controller
{
    public function __invoke(
        StoreTravelOrderRequest $request,
        CreateTravelOrderUseCase $useCase,
    ): JsonResponse {
        $output = $useCase->execute(new CreateTravelOrderInput(
            destination: $request->validated('destination'),
            departureDate: $request->validated('departure_date'),
            returnDate: $request->validated('return_date'),
        ));

        return (new TravelOrderResource($output->order))
            ->response()
            ->setStatusCode(201);
    }
}
