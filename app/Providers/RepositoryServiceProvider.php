<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Ports\AuthenticatedUserPort;
use App\Application\Ports\EventDispatcherPort;
use App\Application\Ports\NotificationPort;
use App\Application\Ports\TravelOrderPersistenceFacadeInterface;
use App\Domain\TravelOrder\Repositories\TravelOrderRepositoryInterface;
use App\Infrastructure\Adapters\LaravelEventDispatcherAdapter;
use App\Infrastructure\Adapters\LaravelNotificationAdapter;
use App\Infrastructure\Adapters\SanctumAuthenticatedUserAdapter;
use App\Infrastructure\Contracts\TravelOrderEloquentTranslatorInterface;
use App\Infrastructure\Contracts\TravelOrderListQueryPort;
use App\Infrastructure\Facades\TravelOrder\TravelOrderEloquentTranslator;
use App\Infrastructure\Facades\TravelOrder\TravelOrderPersistenceFacade;
use App\Infrastructure\Persistence\Queries\EloquentTravelOrderListQueryAdapter;
use App\Infrastructure\Persistence\Repositories\EloquentTravelOrderRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Registra bindings de interfaces de domínio/aplicação para implementações de infraestrutura.
 */
final class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Vincula repositórios, ports e facades às implementações concretas.
     */
    public function register(): void
    {
        $this->app->bind(TravelOrderRepositoryInterface::class, EloquentTravelOrderRepository::class);

        $this->app->bind(AuthenticatedUserPort::class, SanctumAuthenticatedUserAdapter::class);
        $this->app->bind(TravelOrderPersistenceFacadeInterface::class, TravelOrderPersistenceFacade::class);
        $this->app->bind(NotificationPort::class, LaravelNotificationAdapter::class);
        $this->app->bind(EventDispatcherPort::class, LaravelEventDispatcherAdapter::class);

        $this->app->bind(TravelOrderEloquentTranslatorInterface::class, TravelOrderEloquentTranslator::class);
        $this->app->bind(TravelOrderListQueryPort::class, EloquentTravelOrderListQueryAdapter::class);
    }
}
