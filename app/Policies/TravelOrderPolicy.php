<?php

declare(strict_types=1);

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;

/**
 * Regras de autorização para ações em pedidos de viagem.
 */
final class TravelOrderPolicy
{
    /**
     * Apenas administradores podem cancelar pedidos de viagem.
     */
    public function cancel(UserModel $user, TravelOrderModel $order): bool
    {
        return $user->is_admin;
    }

    /**
     * Apenas administradores podem aprovar pedidos de viagem.
     */
    public function approve(UserModel $user, TravelOrderModel $order): bool
    {
        return $user->is_admin;
    }
}
