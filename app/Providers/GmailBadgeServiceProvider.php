<?php

namespace App\Providers;

use App\Models\GoogleAccount;
use App\Models\Email;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class GmailBadgeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share Gmail badge data with all views
        View::composer('layouts.app', function ($view) {
            $gmailBadges = null;
            
            if (Auth::check()) {
                $user = Auth::user();
                $cacheKey = 'gmail_badges_' . $user->id;
                
                // Cache badges for 2 minutes to improve performance
                $gmailBadges = cache()->remember($cacheKey, 120, function () use ($user) {
                    try {
                        // Check if google_accounts table exists
                        $tableExists = Schema::hasTable('google_accounts');
                        if (!$tableExists) {
                            return null; // Table doesn't exist yet
                        }
                        
                        // Get user's active Google accounts
                        $googleAccounts = GoogleAccount::where('user_id', $user->id)
                                                     ->active()
                                                     ->get();

                        if ($googleAccounts->isNotEmpty()) {
                            $accountIds = $googleAccounts->pluck('id');
                            
                            // Check if emails table exists
                            if (!Schema::hasTable('emails')) {
                                return [
                                    'total_accounts' => $googleAccounts->count(),
                                    'has_data' => true,
                                    'unread_count' => 0,
                                    'starred_count' => 0,
                                    'important_count' => 0,
                                ];
                            }
                            
                            return [
                                'unread_count' => Email::whereIn('google_account_id', $accountIds)
                                                       ->inbox()
                                                       ->unread()
                                                       ->count(),
                                'starred_count' => Email::whereIn('google_account_id', $accountIds)
                                                        ->inbox()
                                                        ->starred()
                                                        ->count(),
                                'important_count' => Email::whereIn('google_account_id', $accountIds)
                                                          ->inbox()
                                                          ->important()
                                                          ->count(),
                                'total_accounts' => $googleAccounts->count(),
                                'has_data' => true
                            ];
                        }
                        
                        return null;
                        
                    } catch (\Exception $e) {
                        // If there's any error, just return null silently
                        // This prevents the provider from breaking the app
                        Log::warning('Gmail Badge Provider Error: ' . $e->getMessage());
                        return null;
                    }
                });
            }
            
            $view->with('gmailBadges', $gmailBadges);
        });
    }
}
