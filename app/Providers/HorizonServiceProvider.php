<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Forțează înregistrarea rutelor Horizon
        if (class_exists('Laravel\\Horizon\\Horizon')) {
            Horizon::routeSmsNotificationsTo('15556667777');
            Horizon::routeMailNotificationsTo('example@example.com');
            // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
        }
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            // Allow access in local environment
            if (app()->environment('local')) {
                return true;
            }
            
            // In production, allow access for authenticated users with admin roles
            return $user && (
                $user->hasRole('super_admin') || 
                $user->hasRole('admin') ||
                in_array($user->email, [
                    'admin@crmultra.com',
                    'superadmin@crmultra.com'
                ])
            );
        });
    }
}
