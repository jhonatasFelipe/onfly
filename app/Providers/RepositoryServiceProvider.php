<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Ports\ApiTokenPort;
use App\Application\Ports\AuthenticatedUserPort;
use App\Application\Ports\EventDispatcherPort;
use App\Application\Ports\NotificationPort;
use App\Application\Ports\SessionAuthenticationPort;
use App\Application\Ports\TravelOrderEloquentTranslatorInterface;
use App\Application\Ports\TravelOrderListQueryPort;
use App\Application\Ports\TravelOrderPersistenceFacadeInterface;
use App\Application\Ports\UserAuthenticationPort;
use App\Application\Ports\UserRegistrationPort;
use App\Domain\TravelOrder\Repositories\TravelOrderRepositoryInterface;
use App\Infrastructure\Adapters\Auth\EloquentUserAuthenticationAdapter;
use App\Infrastructure\Adapters\Auth\EloquentUserRegistrationAdapter;
use App\Infrastructure\Adapters\Auth\LaravelSessionAuthenticationAdapter;
use App\Infrastructure\Adapters\Auth\SanctumApiTokenAdapter;
use App\Infrastructure\Adapters\LaravelEventDispatcherAdapter;
use App\Infrastructure\Adapters\LaravelNotificationAdapter;
use App\Infrastructure\Adapters\SanctumAuthenticatedUserAdapter;
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
        $this->app->bind(UserAuthenticationPort::class, EloquentUserAuthenticationAdapter::class);
        $this->app->bind(UserRegistrationPort::class, EloquentUserRegistrationAdapter::class);
        $this->app->bind(ApiTokenPort::class, SanctumApiTokenAdapter::class);
        $this->app->bind(SessionAuthenticationPort::class, LaravelSessionAuthenticationAdapter::class);
        $this->app->bind(TravelOrderPersistenceFacadeInterface::class, TravelOrderPersistenceFacade::class);
        $this->app->bind(NotificationPort::class, LaravelNotificationAdapter::class);
        $this->app->bind(EventDispatcherPort::class, LaravelEventDispatcherAdapter::class);

        $this->app->bind(TravelOrderEloquentTranslatorInterface::class, TravelOrderEloquentTranslator::class);
        $this->app->bind(TravelOrderListQueryPort::class, EloquentTravelOrderListQueryAdapter::class);
    }
}
