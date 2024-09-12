<?php

namespace App\Providers;

use App\View\Breadcrumbs;
use App\View\Composers\NavigationComposer;
use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Breadcrumbs::class, function() {
            return new Breadcrumbs();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Facades\View::composer('layout', NavigationComposer::class);
    }
}
