<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Application\Auth\DTOs\RegisterUserInput;
use App\Application\Auth\UseCases\RegisterUserUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

/**
 * Registra novo usuário via API — delega ao RegisterUserUseCase.
 */
#[Group('Autenticação')]
final class RegisterController extends Controller
{
    /**
     * Registrar usuário
     *
     * Cria uma nova conta de usuário e retorna um token Sanctum para autenticação nos demais endpoints.
     *
     * @unauthenticated
     *
     * @operationId auth.registerUser
     */
    #[Response(201, 'Usuário registrado com token', type: 'array{token: string, user: array{id: int, name: string, email: string}}')]
    #[Response(422, 'Validação falhou', type: 'array{message: string, errors: array<string, string[]>}')]
    #[Response(429, 'Muitas tentativas', type: 'array{message: string}')]
    public function __invoke(RegisterRequest $request, RegisterUserUseCase $useCase): JsonResponse
    {
        $output = $useCase->execute(new RegisterUserInput(
            name: $request->string('name')->value(),
            email: $request->string('email')->value(),
            password: $request->string('password')->value(),
        ));

        return response()->json([
            'token' => $output->token,
            'user' => new UserResource($output->user),
        ], 201);
    }
}
