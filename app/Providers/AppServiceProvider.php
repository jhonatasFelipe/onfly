<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\OpenApi\Scramble\RateLimitOperationExtension;
use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Policies\TravelOrderPolicy;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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

        Gate::define('viewApiDocs', function (?UserModel $user): bool {
            return $user?->is_admin === true;
        });

        $this->configureRateLimiting();

        Scramble::configure()
            ->withOperationTransformers(RateLimitOperationExtension::class)
            ->withDocumentTransformers(function (OpenApi $openApi): void {
                $openApi->secure(SecurityScheme::http('bearer'));
            });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request): Limit {
            return $this->limitFromConfig('api')->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request): Limit {
            return $this->limitFromConfig('auth')->by($request->ip());
        });

        RateLimiter::for('web', function (Request $request): Limit {
            return $this->limitFromConfig('web')->by($request->ip());
        });

        RateLimiter::for('web-login', function (Request $request): Limit {
            return $this->limitFromConfig('web-login')->by($request->ip());
        });

        RateLimiter::for('docs', function (Request $request): Limit {
            return $this->limitFromConfig('docs')->by($request->user()?->id ?: $request->ip());
        });
    }

    private function limitFromConfig(string $name): Limit
    {
        return Limit::perMinutes(
            config()->integer("rate-limiting.{$name}.decay_minutes"),
            config()->integer("rate-limiting.{$name}.max_attempts"),
        );
    }
}
