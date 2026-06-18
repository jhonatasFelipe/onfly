<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\TravelOrder;

use App\Application\TravelOrder\DTOs\CreateTravelOrderInput;
use App\Application\TravelOrder\UseCases\CreateTravelOrderUseCase;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\TravelOrderOpenApiSchemas;
use App\Http\Requests\TravelOrder\StoreTravelOrderRequest;
use App\Http\Resources\TravelOrderResource;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

/**
 * Cria pedido de viagem via API — delega ao CreateTravelOrderUseCase.
 */
#[Group('Pedidos de Viagem')]
final class StoreTravelOrderController extends Controller
{
    /**
     * Criar pedido de viagem
     *
     * Registra um novo pedido com destino e período de viagem. O status inicial é `solicitado`.
     *
     * @operationId travelOrders.create
     */
    #[Response(201, 'Pedido criado', type: TravelOrderOpenApiSchemas::ITEM)]
    #[Response(401, 'Não autenticado', type: 'array{message: string}')]
    #[Response(422, 'Validação falhou', type: 'array{message: string, errors: array<string, string[]>}')]
    #[Response(429, 'Muitas tentativas', type: 'array{message: string}')]
    public function __invoke(
        StoreTravelOrderRequest $request,
        CreateTravelOrderUseCase $useCase,
    ): JsonResponse {
        $output = $useCase->execute(new CreateTravelOrderInput(
            destination: $request->string('destination')->value(),
            departureDate: $request->string('departure_date')->value(),
            returnDate: $request->string('return_date')->value(),
        ));

        return (new TravelOrderResource($output->order))
            ->response()
            ->setStatusCode(201);
    }
}
