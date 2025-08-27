<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SmtpConfig;

class CheckSmtpLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $smtpConfigId = null): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->handleUnauthenticated($request);
        }

        // Get SMTP config from parameter or request
        $smtpConfig = $this->getSmtpConfig($request, $smtpConfigId);
        
        if (!$smtpConfig) {
            return $this->handleMissingSmtpConfig($request);
        }

        // Check if SMTP config is active
        if (!$smtpConfig->is_active) {
            return $this->handleInactiveSmtpConfig($request, $smtpConfig);
        }

        // Check if user owns or has access to this SMTP config
        if (!$this->userCanUseSmtpConfig($user, $smtpConfig)) {
            return $this->handleUnauthorizedSmtpConfig($request, $smtpConfig);
        }

        // Check various SMTP limits
        $limitChecks = [
            'daily' => $this->checkDailyLimit($smtpConfig),
            'hourly' => $this->checkHourlyLimit($smtpConfig),
            'monthly' => $this->checkMonthlyLimit($smtpConfig),
            'concurrent' => $this->checkConcurrentLimit($smtpConfig),
            'provider' => $this->checkProviderSpecificLimits($smtpConfig),
        ];

        foreach ($limitChecks as $type => $check) {
            if (!$check['allowed']) {
                return $this->handleLimitExceeded($request, $type, $check, $smtpConfig);
            }
        }

        // Check SMTP health
        if (!$this->isSmtpHealthy($smtpConfig)) {
            return $this->handleUnhealthySmtp($request, $smtpConfig);
        }

        // Store SMTP config in request for controllers to use
        $request->attributes->set('smtp_config', $smtpConfig);

        return $next($request);
    }

    /**
     * Get SMTP config from request or parameter.
     */
    protected function getSmtpConfig(Request $request, ?string $smtpConfigId): ?SmtpConfig
    {
        // Try to get from route parameter
        if ($smtpConfigId) {
            return SmtpConfig::find($smtpConfigId);
        }

        // Try to get from request data
        if ($request->has('smtp_config_id')) {
            return SmtpConfig::find($request->input('smtp_config_id'));
        }

        // Try to get from route model binding
        if ($request->route('smtpConfig')) {
            return $request->route('smtpConfig');
        }

        // Get user's default active SMTP config
        $user = $request->user();
        return $user->smtpConfigs()->active()->first();
    }

    /**
     * Check if user can use this SMTP config.
     */
    protected function userCanUseSmtpConfig($user, SmtpConfig $smtpConfig): bool
    {
        // Admin can use any SMTP config
        if ($user->hasRole('admin')) {
            return true;
        }

        // User owns this SMTP config
        if ($smtpConfig->created_by === $user->id) {
            return true;
        }

        // Managers can use SMTP configs from their team
        if ($user->hasRole('manager')) {
            return $smtpConfig->creator->hasRole(['agent']) || 
                   $smtpConfig->is_shared;
        }

        // Check if SMTP config is shared with user
        return $smtpConfig->is_shared && $smtpConfig->shared_with_users->contains($user->id);
    }

    /**
     * Check daily sending limit.
     */
    protected function checkDailyLimit(SmtpConfig $smtpConfig): array
    {
        $dailyLimit = $smtpConfig->daily_limit ?? $this->getDefaultDailyLimit($smtpConfig->provider);
        $sentToday = $smtpConfig->getSentCountForPeriod('today');

        return [
            'allowed' => $sentToday < $dailyLimit,
            'current' => $sentToday,
            'limit' => $dailyLimit,
            'remaining' => max(0, $dailyLimit - $sentToday),
            'reset_at' => now()->endOfDay()
        ];
    }

    /**
     * Check hourly sending limit.
     */
    protected function checkHourlyLimit(SmtpConfig $smtpConfig): array
    {
        $hourlyLimit = $smtpConfig->hourly_limit ?? $this->getDefaultHourlyLimit($smtpConfig->provider);
        $sentThisHour = $smtpConfig->getSentCountForPeriod('hour');

        return [
            'allowed' => $sentThisHour < $hourlyLimit,
            'current' => $sentThisHour,
            'limit' => $hourlyLimit,
            'remaining' => max(0, $hourlyLimit - $sentThisHour),
            'reset_at' => now()->endOfHour()
        ];
    }

    /**
     * Check monthly sending limit.
     */
    protected function checkMonthlyLimit(SmtpConfig $smtpConfig): array
    {
        $monthlyLimit = $smtpConfig->monthly_limit ?? $this->getDefaultMonthlyLimit($smtpConfig->provider);
        $sentThisMonth = $smtpConfig->getSentCountForPeriod('month');

        return [
            'allowed' => $sentThisMonth < $monthlyLimit,
            'current' => $sentThisMonth,
            'limit' => $monthlyLimit,
            'remaining' => max(0, $monthlyLimit - $sentThisMonth),
            'reset_at' => now()->endOfMonth()
        ];
    }

    /**
     * Check concurrent sending limit.
     */
    protected function checkConcurrentLimit(SmtpConfig $smtpConfig): array
    {
        $concurrentLimit = $smtpConfig->concurrent_limit ?? 5;
        $currentlySending = $smtpConfig->getCurrentlySendingCount();

        return [
            'allowed' => $currentlySending < $concurrentLimit,
            'current' => $currentlySending,
            'limit' => $concurrentLimit,
            'remaining' => max(0, $concurrentLimit - $currentlySending)
        ];
    }

    /**
     * Check provider-specific limits.
     */
    protected function checkProviderSpecificLimits(SmtpConfig $smtpConfig): array
    {
        return match ($smtpConfig->provider) {
            'sendgrid' => $this->checkSendGridLimits($smtpConfig),
            'mailgun' => $this->checkMailgunLimits($smtpConfig),
            'aws_ses' => $this->checkAwsSesLimits($smtpConfig),
            'gmail' => $this->checkGmailLimits($smtpConfig),
            'outlook' => $this->checkOutlookLimits($smtpConfig),
            default => ['allowed' => true]
        };
    }

    /**
     * Check SendGrid specific limits.
     */
    protected function checkSendGridLimits(SmtpConfig $smtpConfig): array
    {
        // SendGrid has reputation-based limits
        $reputationScore = $smtpConfig->reputation_score ?? 100;
        $baseLimit = $this->getDefaultDailyLimit('sendgrid');
        
        $adjustedLimit = (int) ($baseLimit * ($reputationScore / 100));
        $sentToday = $smtpConfig->getSentCountForPeriod('today');

        return [
            'allowed' => $sentToday < $adjustedLimit,
            'current' => $sentToday,
            'limit' => $adjustedLimit,
            'reputation_score' => $reputationScore
        ];
    }

    /**
     * Check Mailgun specific limits.
     */
    protected function checkMailgunLimits(SmtpConfig $smtpConfig): array
    {
        // Mailgun has domain-based limits
        $domainLimit = $smtpConfig->domain_limit ?? 10000;
        $sentToday = $smtpConfig->getSentCountForPeriod('today');

        return [
            'allowed' => $sentToday < $domainLimit,
            'current' => $sentToday,
            'limit' => $domainLimit
        ];
    }

    /**
     * Check AWS SES specific limits.
     */
    protected function checkAwsSesLimits(SmtpConfig $smtpConfig): array
    {
        // AWS SES has sending rate limits
        $sendingRate = $smtpConfig->sending_rate ?? 1; // emails per second
        $lastSent = $smtpConfig->last_sent_at;
        
        if ($lastSent && $lastSent->diffInSeconds(now()) < (1 / $sendingRate)) {
            return [
                'allowed' => false,
                'rate_limit' => true,
                'wait_seconds' => ceil(1 / $sendingRate)
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Check Gmail specific limits.
     */
    protected function checkGmailLimits(SmtpConfig $smtpConfig): array
    {
        // Gmail has stricter daily limits
        $dailyLimit = 500; // Gmail's daily limit
        $sentToday = $smtpConfig->getSentCountForPeriod('today');

        return [
            'allowed' => $sentToday < $dailyLimit,
            'current' => $sentToday,
            'limit' => $dailyLimit
        ];
    }

    /**
     * Check Outlook specific limits.
     */
    protected function checkOutlookLimits(SmtpConfig $smtpConfig): array
    {
        // Outlook has specific recipient limits
        $recipientLimit = 300; // per day
        $sentToday = $smtpConfig->getSentCountForPeriod('today');

        return [
            'allowed' => $sentToday < $recipientLimit,
            'current' => $sentToday,
            'limit' => $recipientLimit
        ];
    }

    /**
     * Check if SMTP is healthy.
     */
    protected function isSmtpHealthy(SmtpConfig $smtpConfig): bool
    {
        // Check bounce rate
        if ($smtpConfig->bounce_rate > 5) {
            return false;
        }

        // Check recent failures
        $recentFailures = $smtpConfig->getFailureCountForPeriod('hour');
        if ($recentFailures > 10) {
            return false;
        }

        // Check if marked as unhealthy
        if ($smtpConfig->is_healthy === false) {
            return false;
        }

        return true;
    }

    /**
     * Get default daily limit for provider.
     */
    protected function getDefaultDailyLimit(string $provider): int
    {
        return match ($provider) {
            'sendgrid' => 40000,
            'mailgun' => 10000,
            'aws_ses' => 50000,
            'gmail' => 500,
            'outlook' => 300,
            'custom' => 1000,
            default => 5000
        };
    }

    /**
     * Get default hourly limit for provider.
     */
    protected function getDefaultHourlyLimit(string $provider): int
    {
        return match ($provider) {
            'sendgrid' => 2000,
            'mailgun' => 500,
            'aws_ses' => 2500,
            'gmail' => 100,
            'outlook' => 50,
            'custom' => 100,
            default => 250
        };
    }

    /**
     * Get default monthly limit for provider.
     */
    protected function getDefaultMonthlyLimit(string $provider): int
    {
        return match ($provider) {
            'sendgrid' => 1000000,
            'mailgun' => 300000,
            'aws_ses' => 1500000,
            'gmail' => 15000,
            'outlook' => 9000,
            'custom' => 30000,
            default => 150000
        };
    }

    /**
     * Handle unauthenticated request.
     */
    protected function handleUnauthenticated(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        return redirect()->route('login');
    }

    /**
     * Handle missing SMTP config.
     */
    protected function handleMissingSmtpConfig(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'SMTP configuration required',
                'message' => 'Please configure an SMTP provider first.'
            ], 422);
        }

        return redirect()->route('smtp-configs.create')
            ->with('error', 'Please configure an SMTP provider first.');
    }

    /**
     * Handle inactive SMTP config.
     */
    protected function handleInactiveSmtpConfig(Request $request, SmtpConfig $smtpConfig): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'SMTP configuration inactive',
                'message' => 'The selected SMTP configuration is inactive.'
            ], 422);
        }

        return redirect()->route('smtp-configs.index')
            ->with('error', 'The selected SMTP configuration is inactive.');
    }

    /**
     * Handle unauthorized SMTP config.
     */
    protected function handleUnauthorizedSmtpConfig(Request $request, SmtpConfig $smtpConfig): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthorized SMTP configuration',
                'message' => 'You do not have permission to use this SMTP configuration.'
            ], 403);
        }

        return redirect()->route('smtp-configs.index')
            ->with('error', 'You do not have permission to use this SMTP configuration.');
    }

    /**
     * Handle limit exceeded.
     */
    protected function handleLimitExceeded(Request $request, string $type, array $check, SmtpConfig $smtpConfig): Response
    {
        $message = $this->getLimitExceededMessage($type, $check, $smtpConfig);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'SMTP limit exceeded',
                'message' => $message,
                'type' => $type,
                'details' => $check
            ], 429);
        }

        return back()
            ->withErrors(['smtp_limit' => $message])
            ->with('warning', $message);
    }

    /**
     * Handle unhealthy SMTP.
     */
    protected function handleUnhealthySmtp(Request $request, SmtpConfig $smtpConfig): Response
    {
        $message = "SMTP configuration '{$smtpConfig->name}' is currently unhealthy and cannot be used.";

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'SMTP configuration unhealthy',
                'message' => $message
            ], 503);
        }

        return back()
            ->withErrors(['smtp_health' => $message])
            ->with('error', $message);
    }

    /**
     * Get limit exceeded message.
     */
    protected function getLimitExceededMessage(string $type, array $check, SmtpConfig $smtpConfig): string
    {
        return match ($type) {
            'daily' => "Daily email limit reached ({$check['current']}/{$check['limit']}). Limit resets at midnight.",
            'hourly' => "Hourly email limit reached ({$check['current']}/{$check['limit']}). Try again in " . now()->diffInMinutes($check['reset_at']) . " minutes.",
            'monthly' => "Monthly email limit reached ({$check['current']}/{$check['limit']}). Limit resets next month.",
            'concurrent' => "Too many emails being sent concurrently ({$check['current']}/{$check['limit']}). Please wait and try again.",
            'provider' => "Provider-specific limit exceeded for {$smtpConfig->provider}.",
            default => "Email sending limit exceeded."
        };
    }
}
