<?php

declare(strict_types=1);

namespace App\Application\Ports;

use App\Application\Auth\DTOs\AuthUserDto;

/**
 * Porta para autenticação de credenciais de usuário.
 */
interface UserAuthenticationPort
{
    /**
     * @throws \App\Application\Auth\Exceptions\InvalidCredentialsException
     */
    public function authenticate(string $email, string $plainPassword): AuthUserDto;
}
