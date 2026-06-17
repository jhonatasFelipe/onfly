<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters\Auth;

use App\Application\Auth\DTOs\AuthUserDto;
use App\Application\Ports\UserRegistrationPort;
use App\Infrastructure\Persistence\Eloquent\UserModel;

final class EloquentUserRegistrationAdapter implements UserRegistrationPort
{
    public function register(string $name, string $email, string $plainPassword): AuthUserDto
    {
        $user = UserModel::create([
            'name' => $name,
            'email' => $email,
            'password' => $plainPassword,
            'is_admin' => false,
        ]);

        return new AuthUserDto(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            isAdmin: $user->is_admin,
        );
    }
}
