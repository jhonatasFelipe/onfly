<?php

declare(strict_types=1);

namespace App\Application\Ports;

/**
 * Porta para despacho de eventos de domínio e aplicação.
 */
interface EventDispatcherPort
{
    /**
     * Despacha um evento para os listeners registrados.
     */
    public function dispatch(object $event): void;
}
