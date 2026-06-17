<?php

declare(strict_types=1);

namespace App\Application\Auth\DTOs;

final readonly class LoginUserOutput
{
    public function __construct(
        public string $token,
        public AuthUserDto $user,
    ) {}
}
