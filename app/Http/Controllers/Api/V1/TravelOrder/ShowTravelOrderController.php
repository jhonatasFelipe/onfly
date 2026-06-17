<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\TravelOrder;

use App\Application\TravelOrder\DTOs\ShowTravelOrderInput;
use App\Application\TravelOrder\UseCases\ShowTravelOrderUseCase;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\TravelOrderOpenApiSchemas;
use App\Http\Resources\TravelOrderResource;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

/**
 * Exibe um pedido de viagem via API — delega ao ShowTravelOrderUseCase.
 */
#[Group('Pedidos de Viagem')]
final class ShowTravelOrderController extends Controller
{
    /**
     * Consultar pedido de viagem
     *
     * Retorna os detalhes de um pedido específico pelo UUID. Usuários comuns só acessam os próprios pedidos.
     *
     * @operationId travelOrders.show
     */
    #[PathParameter('id', description: 'UUID do pedido de viagem', type: 'string', format: 'uuid')]
    #[Response(200, 'Detalhes do pedido', type: TravelOrderOpenApiSchemas::ITEM)]
    #[Response(401, 'Não autenticado', type: 'array{message: string}')]
    #[Response(403, 'Sem permissão para visualizar o pedido', type: 'array{message: string}')]
    #[Response(404, 'Pedido não encontrado', type: 'array{message: string}')]
    #[Response(429, 'Muitas tentativas', type: 'array{message: string}')]
    public function __invoke(string $id, ShowTravelOrderUseCase $useCase): JsonResponse
    {
        $output = $useCase->execute(new ShowTravelOrderInput($id));

        return (new TravelOrderResource($output->order))->response();
    }
}
