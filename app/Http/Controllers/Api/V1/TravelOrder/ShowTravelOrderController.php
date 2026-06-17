<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\TravelOrder;

use App\Application\TravelOrder\DTOs\ShowTravelOrderInput;
use App\Application\TravelOrder\UseCases\ShowTravelOrderUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\TravelOrderResource;
use Illuminate\Http\JsonResponse;

/**
 * Exibe um pedido de viagem via API — delega ao ShowTravelOrderUseCase.
 */
final class ShowTravelOrderController extends Controller
{
    public function __invoke(string $id, ShowTravelOrderUseCase $useCase): JsonResponse
    {
        $output = $useCase->execute(new ShowTravelOrderInput($id));

        return (new TravelOrderResource($output->order))->response();
    }
}
