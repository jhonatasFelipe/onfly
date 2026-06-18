<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\TravelOrder;

use App\Application\TravelOrder\DTOs\ListTravelOrdersInput;
use App\Application\TravelOrder\UseCases\ListTravelOrdersUseCase;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\TravelOrderOpenApiSchemas;
use App\Http\Requests\TravelOrder\ListTravelOrdersRequest;
use App\Http\Resources\PaginatedTravelOrderResource;
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
     * Retorna os pedidos do usuário autenticado de forma paginada. Administradores visualizam todos os pedidos. Aceita filtros opcionais por status, destino e datas.
     *
     * @operationId travelOrders.list
     */
    #[Response(200, 'Lista paginada de pedidos', type: TravelOrderOpenApiSchemas::LIST)]
    #[Response(401, 'Não autenticado', type: 'array{message: string}')]
    #[Response(429, 'Muitas tentativas', type: 'array{message: string}')]
    public function __invoke(
        ListTravelOrdersRequest $request,
        ListTravelOrdersUseCase $useCase,
    ): JsonResponse {
        $output = $useCase->execute(new ListTravelOrdersInput(
            page: $request->integer('page'),
            perPage: $request->integer('per_page'),
            status: $request->filled('status') ? $request->string('status')->value() : null,
            destination: $request->filled('destination') ? $request->string('destination')->value() : null,
            createdFrom: $request->filled('created_from') ? $request->string('created_from')->value() : null,
            createdTo: $request->filled('created_to') ? $request->string('created_to')->value() : null,
            departureFrom: $request->filled('departure_from') ? $request->string('departure_from')->value() : null,
            departureTo: $request->filled('departure_to') ? $request->string('departure_to')->value() : null,
        ));

        return (new PaginatedTravelOrderResource($output->page))->response();
    }
}
