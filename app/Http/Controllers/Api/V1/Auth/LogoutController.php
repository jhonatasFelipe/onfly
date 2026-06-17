<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Application\Auth\UseCases\LogoutUserUseCase;
use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

/**
 * Revoga o token Sanctum do usuário autenticado.
 */
#[Group('Autenticação')]
final class LogoutController extends Controller
{
    /**
     * Encerrar sessão
     *
     * Revoga o token Sanctum atual do usuário autenticado, invalidando o acesso à API.
     *
     * @operationId auth.logoutUser
     */
    #[Response(200, 'Logout realizado', type: 'array{message: string}')]
    #[Response(401, 'Não autenticado', type: 'array{message: string}')]
    #[Response(429, 'Muitas tentativas', type: 'array{message: string}')]
    public function __invoke(LogoutUserUseCase $useCase): JsonResponse
    {
        $useCase->execute();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
