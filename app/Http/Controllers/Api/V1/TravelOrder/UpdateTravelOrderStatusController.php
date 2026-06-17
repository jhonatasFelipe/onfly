<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\TravelOrder;

use App\Application\TravelOrder\DTOs\UpdateTravelOrderStatusInput;
use App\Application\TravelOrder\UseCases\UpdateTravelOrderStatusUseCase;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\TravelOrderOpenApiSchemas;
use App\Http\Requests\TravelOrder\UpdateTravelOrderStatusRequest;
use App\Http\Resources\TravelOrderResource;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

/**
 * Atualiza status de pedido de viagem via API — delega ao UpdateTravelOrderStatusUseCase.
 */
#[Group('Pedidos de Viagem')]
final class UpdateTravelOrderStatusController extends Controller
{
    /**
     * Atualizar status do pedido
     *
     * Aprova ou cancela um pedido de viagem. Apenas administradores podem alterar o status.
     *
     * @operationId travelOrders.updateStatus
     */
    #[PathParameter('id', description: 'UUID do pedido de viagem', type: 'string', format: 'uuid')]
    #[Response(200, 'Status atualizado', type: TravelOrderOpenApiSchemas::ITEM)]
    #[Response(401, 'Não autenticado', type: 'array{message: string}')]
    #[Response(403, 'Sem permissão para alterar o status', type: 'array{message: string}')]
    #[Response(404, 'Pedido não encontrado', type: 'array{message: string}')]
    #[Response(409, 'Transição de status inválida', type: 'array{message: string}')]
    #[Response(422, 'Validação falhou', type: 'array{message: string, errors: array<string, string[]>}')]
    #[Response(429, 'Muitas tentativas', type: 'array{message: string}')]
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
