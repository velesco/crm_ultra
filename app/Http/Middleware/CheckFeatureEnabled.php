<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // Check if feature is enabled globally
        $globallyEnabled = $this->isFeatureEnabledGlobally($feature);

        if (! $globallyEnabled) {
            return $this->handleDisabledFeature($request, $feature);
        }

        // Check if user has access to this feature
        if (! $this->userHasFeatureAccess($request->user(), $feature)) {
            return $this->handleUnauthorizedAccess($request, $feature);
        }

        // Check feature-specific conditions
        if (! $this->checkFeatureConditions($request, $feature)) {
            return $this->handleFeatureConditionsNotMet($request, $feature);
        }

        return $next($request);
    }

    /**
     * Check if feature is enabled globally in settings.
     */
    protected function isFeatureEnabledGlobally(string $feature): bool
    {
        // Check application config first
        if (! config("features.{$feature}.enabled", true)) {
            return false;
        }

        // Check database settings
        $setting = \App\Models\Setting::where('key', "feature_{$feature}_enabled")->first();
        if ($setting) {
            return (bool) $setting->value;
        }

        // Default feature states
        return match ($feature) {
            'email_campaigns' => config('features.email_campaigns.enabled', true),
            'sms' => config('features.sms.enabled', true),
            'whatsapp' => config('features.whatsapp.enabled', true),
            'google_sheets' => config('features.google_sheets.enabled', true),
            'data_import' => config('features.data_import.enabled', true),
            'reports' => config('features.reports.enabled', true),
            'api' => config('features.api.enabled', false),
            'webhooks' => config('features.webhooks.enabled', false),
            'bulk_operations' => config('features.bulk_operations.enabled', true),
            'advanced_segments' => config('features.advanced_segments.enabled', true),
            default => true
        };
    }

    /**
     * Check if user has access to this feature.
     */
    protected function userHasFeatureAccess($user, string $feature): bool
    {
        if (! $user) {
            return false;
        }

        // Super admin has access to everything
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Check user's plan/subscription limits
        $plan = $user->subscription_plan ?? 'free';

        return match ($feature) {
            'email_campaigns' => in_array($plan, ['free', 'basic', 'pro', 'enterprise']),
            'sms' => in_array($plan, ['basic', 'pro', 'enterprise']),
            'whatsapp' => in_array($plan, ['pro', 'enterprise']),
            'google_sheets' => in_array($plan, ['basic', 'pro', 'enterprise']),
            'data_import' => in_array($plan, ['free', 'basic', 'pro', 'enterprise']),
            'reports' => in_array($plan, ['basic', 'pro', 'enterprise']),
            'api' => in_array($plan, ['pro', 'enterprise']),
            'webhooks' => in_array($plan, ['pro', 'enterprise']),
            'bulk_operations' => in_array($plan, ['basic', 'pro', 'enterprise']),
            'advanced_segments' => in_array($plan, ['pro', 'enterprise']),
            default => true
        };
    }

    /**
     * Check feature-specific conditions.
     */
    protected function checkFeatureConditions(Request $request, string $feature): bool
    {
        $user = $request->user();

        return match ($feature) {
            'email_campaigns' => $this->checkEmailCampaignConditions($user),
            'sms' => $this->checkSmsConditions($user),
            'whatsapp' => $this->checkWhatsAppConditions($user),
            'google_sheets' => $this->checkGoogleSheetsConditions($user),
            'data_import' => $this->checkDataImportConditions($user),
            default => true
        };
    }

    /**
     * Check email campaign specific conditions.
     */
    protected function checkEmailCampaignConditions($user): bool
    {
        // Check if user has at least one active SMTP config
        if (! $user->hasActiveSmtpConfig()) {
            session()->flash('feature_warning', 'You need to configure at least one SMTP provider to use email campaigns.');

            return false;
        }

        return true;
    }

    /**
     * Check SMS specific conditions.
     */
    protected function checkSmsConditions($user): bool
    {
        // Check if user has at least one active SMS provider
        if (! $user->hasActiveSmsProvider()) {
            session()->flash('feature_warning', 'You need to configure at least one SMS provider to send SMS messages.');

            return false;
        }

        return true;
    }

    /**
     * Check WhatsApp specific conditions.
     */
    protected function checkWhatsAppConditions($user): bool
    {
        // Check if user has at least one active WhatsApp session
        if (! $user->hasActiveWhatsAppSession()) {
            session()->flash('feature_warning', 'You need to set up and connect a WhatsApp session first.');

            return false;
        }

        return true;
    }

    /**
     * Check Google Sheets specific conditions.
     */
    protected function checkGoogleSheetsConditions($user): bool
    {
        // Check if Google Sheets integration is configured
        $integration = $user->googleSheetsIntegrations()->active()->first();
        if (! $integration) {
            session()->flash('feature_warning', 'You need to set up Google Sheets integration first.');

            return false;
        }

        return true;
    }

    /**
     * Check data import specific conditions.
     */
    protected function checkDataImportConditions($user): bool
    {
        // Check daily import limits
        $dailyImports = $user->dataImports()
            ->whereDate('created_at', today())
            ->count();

        $limit = match ($user->subscription_plan ?? 'free') {
            'free' => 5,
            'basic' => 20,
            'pro' => 100,
            'enterprise' => 500,
            default => 5
        };

        if ($dailyImports >= $limit) {
            session()->flash('feature_warning', "Daily import limit reached ({$limit} imports per day).");

            return false;
        }

        return true;
    }

    /**
     * Handle disabled feature.
     */
    protected function handleDisabledFeature(Request $request, string $feature): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Feature disabled',
                'message' => "The {$feature} feature is currently disabled.",
                'feature' => $feature,
            ], 403);
        }

        return redirect()->route('dashboard')
            ->with('error', "The {$feature} feature is currently disabled. Please contact your administrator.");
    }

    /**
     * Handle unauthorized access.
     */
    protected function handleUnauthorizedAccess(Request $request, string $feature): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Feature not available',
                'message' => "The {$feature} feature is not available in your current plan.",
                'feature' => $feature,
            ], 403);
        }

        return redirect()->route('dashboard')
            ->with('error', "The {$feature} feature is not available in your current plan. Please upgrade to access this feature.");
    }

    /**
     * Handle feature conditions not met.
     */
    protected function handleFeatureConditionsNotMet(Request $request, string $feature): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Feature requirements not met',
                'message' => session('feature_warning', "Please complete the required setup for {$feature}."),
                'feature' => $feature,
            ], 422);
        }

        $redirectRoute = match ($feature) {
            'email_campaigns' => 'smtp-configs.create',
            'sms' => 'sms.providers.create',
            'whatsapp' => 'whatsapp.sessions.create',
            'google_sheets' => 'google.sheets.auth',
            default => 'dashboard'
        };

        return redirect()->route($redirectRoute)
            ->with('warning', session('feature_warning', "Please complete the required setup for {$feature}."));
    }
}
