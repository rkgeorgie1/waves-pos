<?php

namespace App\Providers;

use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantContext;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cashier::useCustomerModel(Tenant::class);
        Cashier::useSubscriptionModel(Subscription::class);

        Paginator::useBootstrapFive();

        if (app()->isProduction()) {
            URL::forceScheme('https');
            Paginator::currentPathResolver(fn () => secure_url(request()->path()));
        }
    }
}
