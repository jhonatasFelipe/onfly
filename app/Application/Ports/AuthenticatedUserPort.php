<?php

declare(strict_types=1);

namespace App\Application\Ports;

/**
 * Porta para obter o usuário autenticado na requisição atual.
 */
interface AuthenticatedUserPort
{
    public function userId(): int;

    public function requesterName(): string;

    public function isAdmin(): bool;
}
