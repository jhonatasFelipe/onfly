<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que restringe acesso a rotas exclusivas de administradores.
 */
final class EnsureUserIsAdmin
{
    /**
     * Verifica se o usuário autenticado possui perfil admin antes de prosseguir.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof UserModel || ! $user->is_admin) {
            abort(403, 'Administrator access required.');
        }

        return $next($request);
    }
}
