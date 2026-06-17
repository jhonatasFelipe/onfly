<?php

declare(strict_types=1);

namespace App\Application\Ports;

/**
 * Porta para autenticação via sessão web.
 */
interface SessionAuthenticationPort
{
    public function login(int $userId): void;

    public function logout(): void;
}
