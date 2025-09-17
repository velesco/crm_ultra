<?php

namespace App\Providers;

use App\Services\GmailService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Gmail Service as singleton
        $this->app->singleton(GmailService::class, function ($app) {
            return new GmailService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        require_once app_path('helpers.php');
    }
}
