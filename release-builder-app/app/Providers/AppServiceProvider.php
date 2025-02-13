<?php

namespace App\Providers;

use App\Services\TaskTracker\Api\Mayven;
use App\Services\TaskTracker\TaskTrackerInterface;
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

        $taskTracker = match(config('tasktracker.default')) {
            // TODO: it is possible to extend list of available TaskTrackers API
            'mayven' => Mayven::class,
            default => null
        };
        $this->app->bind(TaskTrackerInterface::class, $taskTracker);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Facades\View::composer('layout', NavigationComposer::class);
    }
}
