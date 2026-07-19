<?php

namespace App\Providers;

use App\Support\StripeHttpClientConfigurator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        StripeHttpClientConfigurator::configure();
    }
}
