<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters;

use App\Application\Ports\AuthenticatedUserPort;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Support\Facades\Auth;

/**
 * Resolve o usuário autenticado via Sanctum para uso nos use cases.
 */
final class SanctumAuthenticatedUserAdapter implements AuthenticatedUserPort
{
    public function userId(): int
    {
        $user = $this->authenticatedUser();

        return $user->id;
    }

    public function requesterName(): string
    {
        $user = $this->authenticatedUser();

        return $user->name;
    }

    public function isAdmin(): bool
    {
        return $this->authenticatedUser()->is_admin;
    }

    private function authenticatedUser(): UserModel
    {
        $user = Auth::user();

        if (! $user instanceof UserModel) {
            throw new \RuntimeException('Authenticated user is required.');
        }

        return $user;
    }
}
