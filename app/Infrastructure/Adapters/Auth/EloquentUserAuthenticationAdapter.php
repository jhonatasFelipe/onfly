<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters\Auth;

use App\Application\Auth\DTOs\AuthUserDto;
use App\Application\Auth\Exceptions\InvalidCredentialsException;
use App\Application\Ports\UserAuthenticationPort;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Support\Facades\Auth;

final class EloquentUserAuthenticationAdapter implements UserAuthenticationPort
{
    public function authenticate(string $email, string $plainPassword): AuthUserDto
    {
        if (! Auth::attempt(['email' => $email, 'password' => $plainPassword])) {
            throw new InvalidCredentialsException();
        }

        $user = Auth::user();

        if (! $user instanceof UserModel) {
            Auth::logout();

            throw new InvalidCredentialsException();
        }

        return $this->toDto($user);
    }

    private function toDto(UserModel $user): AuthUserDto
    {
        return new AuthUserDto(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            isAdmin: $user->is_admin,
        );
    }
}
