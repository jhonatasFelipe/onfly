<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters\Auth;

use App\Application\Ports\SessionAuthenticationPort;
use Illuminate\Support\Facades\Auth;

final class LaravelSessionAuthenticationAdapter implements SessionAuthenticationPort
{
    public function login(int $userId): void
    {
        Auth::loginUsingId($userId);
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
