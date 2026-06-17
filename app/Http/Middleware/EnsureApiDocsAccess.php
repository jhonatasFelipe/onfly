<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe acesso à documentação OpenAPI a administradores autenticados.
 *
 * Em ambiente local o acesso é público (comportamento padrão do Scramble para desenvolvimento).
 */
final class EnsureApiDocsAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local')) {
            return $next($request);
        }

        $user = $this->resolveAuthenticatedUser($request);

        if ($user !== null && Gate::forUser($user)->allows('viewApiDocs')) {
            return $next($request);
        }

        abort(403);
    }

    private function resolveAuthenticatedUser(Request $request): ?UserModel
    {
        $user = $request->user() ?? $request->user('sanctum');

        return $user instanceof UserModel ? $user : null;
    }
}
