<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters;

use App\Application\Ports\AuthenticatedUserPort;
use App\Domain\TravelOrder\ValueObjects\RequesterName;
use App\Domain\TravelOrder\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Implementação Sanctum de {@see AuthenticatedUserPort}.
 */
final class SanctumAuthenticatedUserAdapter implements AuthenticatedUserPort
{
    public function userId(): UserId
    {
        $user = $this->user();

        return UserId::fromInt($user->id);
    }

    public function requesterName(): RequesterName
    {
        $user = $this->user();

        return RequesterName::fromString($user->name);
    }

    public function isAdmin(): bool
    {
        return $this->user()->is_admin;
    }

    private function user(): UserModel
    {
        $user = Auth::user();

        if (! $user instanceof UserModel) {
            throw new RuntimeException('Authenticated user is required.');
        }

        return $user;
    }
}
