<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\TravelOrder;

use App\Application\TravelOrder\DTOs\ListTravelOrdersInput;
use App\Application\TravelOrder\UseCases\ListTravelOrdersUseCase;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\TravelOrderOpenApiSchemas;
use App\Http\Requests\TravelOrder\ListTravelOrdersRequest;
use App\Http\Resources\TravelOrderResource;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

/**
 * Lista pedidos de viagem via API — delega ao ListTravelOrdersUseCase.
 */
#[Group('Pedidos de Viagem')]
final class ListTravelOrdersController extends Controller
{
    /**
     * Listar pedidos de viagem
     *
     * Retorna os pedidos do usuário autenticado. Administradores visualizam todos os pedidos. Aceita filtros opcionais por status, destino e datas.
     *
     * @operationId travelOrders.list
     */
    #[Response(200, 'Lista de pedidos', type: TravelOrderOpenApiSchemas::LIST)]
    #[Response(401, 'Não autenticado', type: 'array{message: string}')]
    #[Response(429, 'Muitas tentativas', type: 'array{message: string}')]
    public function __invoke(
        ListTravelOrdersRequest $request,
        ListTravelOrdersUseCase $useCase,
    ): JsonResponse {
        $output = $useCase->execute(new ListTravelOrdersInput(
            status: $request->validated('status'),
            destination: $request->validated('destination'),
            createdFrom: $request->validated('created_from'),
            createdTo: $request->validated('created_to'),
            departureFrom: $request->validated('departure_from'),
            departureTo: $request->validated('departure_to'),
        ));

        return response()->json([
            'data' => TravelOrderResource::collection(collect($output->orders->all())),
        ]);
    }
}
