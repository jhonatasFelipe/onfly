<?php

declare(strict_types=1);

namespace App\Application\Ports;

use App\Application\Auth\DTOs\AuthUserDto;
use App\Application\Auth\Exceptions\InvalidCredentialsException;

/**
 * Porta para autenticação de credenciais de usuário.
 */
interface UserAuthenticationPort
{
    /**
     * @throws InvalidCredentialsException
     */
    public function authenticate(string $email, string $plainPassword): AuthUserDto;
}
