<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters;

use App\Application\Ports\EventDispatcherPort;

/**
 * Implementação Laravel de {@see EventDispatcherPort} via helper event().
 */
final class LaravelEventDispatcherAdapter implements EventDispatcherPort
{
    public function dispatch(object $event): void
    {
        event($event);
    }
}
