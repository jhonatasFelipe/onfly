<?php

declare(strict_types=1);

namespace App\Application\Ports;

use App\Application\Auth\DTOs\AuthUserDto;

/**
 * Porta para registro de novos usuários.
 */
interface UserRegistrationPort
{
    public function register(string $name, string $email, string $plainPassword): AuthUserDto;
}
