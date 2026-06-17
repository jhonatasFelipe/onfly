<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\TravelOrder\Listeners\SendTravelOrderApprovedNotificationListener;
use App\Application\TravelOrder\Listeners\SendTravelOrderCancelledNotificationListener;
use App\Domain\TravelOrder\Events\TravelOrderApproved;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Registra listeners para eventos de domínio de pedidos de viagem.
 */
final class EventServiceProvider extends ServiceProvider
{
    /**
     * Mapeia eventos de aprovação e cancelamento aos listeners de notificação.
     */
    public function boot(): void
    {
        Event::listen(
            TravelOrderApproved::class,
            [SendTravelOrderApprovedNotificationListener::class, 'handle'],
        );

        Event::listen(
            TravelOrderCancelled::class,
            [SendTravelOrderCancelledNotificationListener::class, 'handle'],
        );
    }
}
