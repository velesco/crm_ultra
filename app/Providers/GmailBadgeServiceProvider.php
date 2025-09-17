<?php

namespace App\Providers;

use App\Models\GoogleAccount;
use App\Models\Email;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

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
                    // Get user's active Google accounts
                    $googleAccounts = GoogleAccount::where('user_id', $user->id)
                                                 ->active()
                                                 ->get();

                    if ($googleAccounts->isNotEmpty()) {
                        $accountIds = $googleAccounts->pluck('id');
                        
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
                });
            }
            
            $view->with('gmailBadges', $gmailBadges);
        });
    }
}
