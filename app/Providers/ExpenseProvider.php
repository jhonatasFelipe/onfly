<?php

namespace App\Providers;

use App\Models\Expenses;
use App\Services\ExpenseService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ExpenseProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ExpenseService::class, function (Application $app){
            return new ExpenseService($app->make(Expenses::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
