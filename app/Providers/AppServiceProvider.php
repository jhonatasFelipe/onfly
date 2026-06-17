<?php

namespace App\Providers;

use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
use App\Policies\TravelOrderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra serviços da aplicação.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inicializa serviços da aplicação (policies, etc.).
     */
    public function boot(): void
    {
        Gate::policy(TravelOrderModel::class, TravelOrderPolicy::class);
    }
}
