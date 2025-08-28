<?php

namespace App\Providers;

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
        // Add formatBytes helper function
        if (!function_exists('formatBytes')) {
            function formatBytes($bytes, $precision = 2) {
                if ($bytes == 0) {
                    return '0 B';
                }
                
                $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
                $base = log($bytes, 1024);
                
                return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
            }
        }
    }
}
