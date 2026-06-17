<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters\Auth;

use App\Application\Ports\ApiTokenPort;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

final class SanctumApiTokenAdapter implements ApiTokenPort
{
    public function createForUser(int $userId, string $tokenName): string
    {
        $user = UserModel::findOrFail($userId);

        return $user->createToken($tokenName)->plainTextToken;
    }

    public function revokeCurrent(): void
    {
        $user = Auth::user();

        if (! $user instanceof UserModel) {
            throw new RuntimeException('Authenticated user is required.');
        }

        $user->currentAccessToken()?->delete();
    }
}
