<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Application\Auth\DTOs\LoginUserInput;
use App\Application\Auth\UseCases\LoginUserUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

/**
 * Autentica usuário via API — delega ao LoginUserUseCase.
 */
#[Group('Autenticação')]
final class LoginController extends Controller
{
    /**
     * Autenticar usuário
     *
     * Valida email e senha e retorna um token Bearer para uso nos endpoints protegidos.
     *
     * @unauthenticated
     * @operationId auth.loginUser
     */
    #[Response(200, 'Login realizado com token', type: 'array{token: string, user: array{id: int, name: string, email: string}}')]
    #[Response(401, 'Credenciais inválidas', type: 'array{message: string}')]
    #[Response(422, 'Validação falhou', type: 'array{message: string, errors: array<string, string[]>}')]
    #[Response(429, 'Muitas tentativas', type: 'array{message: string}')]
    public function __invoke(LoginRequest $request, LoginUserUseCase $useCase): JsonResponse
    {
        $output = $useCase->execute(new LoginUserInput(
            email: $request->validated('email'),
            password: $request->validated('password'),
        ));

        return response()->json([
            'token' => $output->token,
            'user' => new UserResource($output->user),
        ]);
    }
}
