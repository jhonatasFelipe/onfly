<?php

declare(strict_types=1);

namespace App\Application\Ports;

use App\Domain\TravelOrder\ValueObjects\RequesterName;
use App\Domain\TravelOrder\ValueObjects\UserId;

/**
 * Porta de acesso ao usuário autenticado na camada de aplicação.
 */
interface AuthenticatedUserPort
{
    /**
     * Retorna o identificador do usuário autenticado.
     */
    public function userId(): UserId;

    /**
     * Retorna o nome do solicitante associado ao usuário autenticado.
     */
    public function requesterName(): RequesterName;

    /**
     * Indica se o usuário autenticado possui perfil administrador.
     */
    public function isAdmin(): bool;
}
