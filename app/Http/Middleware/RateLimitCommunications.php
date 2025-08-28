<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitCommunications
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type = 'general'): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Get rate limits based on user plan and communication type
        $limits = $this->getRateLimits($user, $type);

        // Check each limit tier
        foreach ($limits as $limitKey => $limitData) {
            $key = $this->buildRateLimitKey($user->id, $type, $limitKey);

            if (RateLimiter::tooManyAttempts($key, $limitData['max_attempts'])) {
                return $this->handleRateLimitExceeded($request, $type, $limitData);
            }
        }

        // Execute the request
        $response = $next($request);

        // Increment rate limit counters only for successful requests
        if ($response->getStatusCode() < 400) {
            foreach ($limits as $limitKey => $limitData) {
                $key = $this->buildRateLimitKey($user->id, $type, $limitKey);
                RateLimiter::hit($key, $limitData['decay_seconds']);
            }

            // Log communication for analytics
            $this->logCommunication($user, $type, $request);
        }

        return $response;
    }

    /**
     * Get rate limits based on user plan and communication type.
     */
    protected function getRateLimits($user, string $type): array
    {
        $plan = $user->subscription_plan ?? 'free';

        $baseLimits = match ($type) {
            'email' => $this->getEmailLimits($plan),
            'sms' => $this->getSmsLimits($plan),
            'whatsapp' => $this->getWhatsAppLimits($plan),
            'api' => $this->getApiLimits($plan),
            default => $this->getGeneralLimits($plan)
        };

        // Apply user-specific multipliers if they have premium features
        if ($user->hasRole(['admin', 'manager'])) {
            foreach ($baseLimits as $key => $limit) {
                $baseLimits[$key]['max_attempts'] = (int) ($limit['max_attempts'] * 1.5);
            }
        }

        return $baseLimits;
    }

    /**
     * Get email rate limits by plan.
     */
    protected function getEmailLimits(string $plan): array
    {
        return match ($plan) {
            'free' => [
                'per_minute' => ['max_attempts' => 5, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 50, 'decay_seconds' => 3600],
                'per_day' => ['max_attempts' => 100, 'decay_seconds' => 86400],
            ],
            'basic' => [
                'per_minute' => ['max_attempts' => 15, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 200, 'decay_seconds' => 3600],
                'per_day' => ['max_attempts' => 1000, 'decay_seconds' => 86400],
            ],
            'pro' => [
                'per_minute' => ['max_attempts' => 50, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 1000, 'decay_seconds' => 3600],
                'per_day' => ['max_attempts' => 10000, 'decay_seconds' => 86400],
            ],
            'enterprise' => [
                'per_minute' => ['max_attempts' => 200, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 5000, 'decay_seconds' => 3600],
                'per_day' => ['max_attempts' => 50000, 'decay_seconds' => 86400],
            ],
            default => $this->getEmailLimits('free')
        };
    }

    /**
     * Get SMS rate limits by plan.
     */
    protected function getSmsLimits(string $plan): array
    {
        return match ($plan) {
            'free' => [
                'per_minute' => ['max_attempts' => 2, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 10, 'decay_seconds' => 3600],
                'per_day' => ['max_attempts' => 25, 'decay_seconds' => 86400],
            ],
            'basic' => [
                'per_minute' => ['max_attempts' => 5, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 50, 'decay_seconds' => 3600],
                'per_day' => ['max_attempts' => 200, 'decay_seconds' => 86400],
            ],
            'pro' => [
                'per_minute' => ['max_attempts' => 20, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 300, 'decay_seconds' => 3600],
                'per_day' => ['max_attempts' => 2000, 'decay_seconds' => 86400],
            ],
            'enterprise' => [
                'per_minute' => ['max_attempts' => 100, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 1500, 'decay_seconds' => 3600],
                'per_day' => ['max_attempts' => 15000, 'decay_seconds' => 86400],
            ],
            default => $this->getSmsLimits('free')
        };
    }

    /**
     * Get WhatsApp rate limits by plan.
     */
    protected function getWhatsAppLimits(string $plan): array
    {
        return match ($plan) {
            'free' => [
                'per_minute' => ['max_attempts' => 3, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 20, 'decay_seconds' => 3600],
                'per_day' => ['max_attempts' => 100, 'decay_seconds' => 86400],
            ],
            'basic' => [
                'per_minute' => ['max_attempts' => 10, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 100, 'decay_seconds' => 3600],
                'per_day' => ['max_attempts' => 500, 'decay_seconds' => 86400],
            ],
            'pro' => [
                'per_minute' => ['max_attempts' => 30, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 500, 'decay_seconds' => 3600],
                'per_day' => ['max_attempts' => 3000, 'decay_seconds' => 86400],
            ],
            'enterprise' => [
                'per_minute' => ['max_attempts' => 100, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 2000, 'decay_seconds' => 3600],
                'per_day' => ['max_attempts' => 20000, 'decay_seconds' => 86400],
            ],
            default => $this->getWhatsAppLimits('free')
        };
    }

    /**
     * Get API rate limits by plan.
     */
    protected function getApiLimits(string $plan): array
    {
        return match ($plan) {
            'free' => [
                'per_minute' => ['max_attempts' => 10, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 100, 'decay_seconds' => 3600],
            ],
            'basic' => [
                'per_minute' => ['max_attempts' => 30, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 500, 'decay_seconds' => 3600],
            ],
            'pro' => [
                'per_minute' => ['max_attempts' => 100, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 2000, 'decay_seconds' => 3600],
            ],
            'enterprise' => [
                'per_minute' => ['max_attempts' => 500, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 10000, 'decay_seconds' => 3600],
            ],
            default => $this->getApiLimits('free')
        };
    }

    /**
     * Get general rate limits by plan.
     */
    protected function getGeneralLimits(string $plan): array
    {
        return match ($plan) {
            'free' => [
                'per_minute' => ['max_attempts' => 20, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 200, 'decay_seconds' => 3600],
            ],
            'basic' => [
                'per_minute' => ['max_attempts' => 50, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 1000, 'decay_seconds' => 3600],
            ],
            'pro' => [
                'per_minute' => ['max_attempts' => 150, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 3000, 'decay_seconds' => 3600],
            ],
            'enterprise' => [
                'per_minute' => ['max_attempts' => 500, 'decay_seconds' => 60],
                'per_hour' => ['max_attempts' => 10000, 'decay_seconds' => 3600],
            ],
            default => $this->getGeneralLimits('free')
        };
    }

    /**
     * Build rate limit key.
     */
    protected function buildRateLimitKey(int $userId, string $type, string $period): string
    {
        return "rate_limit:{$type}:{$userId}:{$period}";
    }

    /**
     * Handle rate limit exceeded.
     */
    protected function handleRateLimitExceeded(Request $request, string $type, array $limitData): Response
    {
        $retryAfter = $limitData['decay_seconds'];
        $message = "Rate limit exceeded for {$type}. Try again in ".$this->formatTime($retryAfter).'.';

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'message' => $message,
                'retry_after' => $retryAfter,
                'type' => $type,
            ], 429)->header('Retry-After', $retryAfter);
        }

        return back()
            ->withErrors(['rate_limit' => $message])
            ->with('warning', $message);
    }

    /**
     * Log communication for analytics.
     */
    protected function logCommunication($user, string $type, Request $request): void
    {
        $cacheKey = "comm_stats:{$user->id}:".now()->format('Y-m-d');
        $stats = Cache::get($cacheKey, []);

        if (! isset($stats[$type])) {
            $stats[$type] = 0;
        }

        $stats[$type]++;

        Cache::put($cacheKey, $stats, now()->addDays(7));

        // Log to database for detailed analytics (async)
        dispatch(function () use ($user, $type, $request) {
            \App\Models\CommunicationLog::create([
                'user_id' => $user->id,
                'type' => $type,
                'endpoint' => $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        })->afterResponse();
    }

    /**
     * Format time for human readable format.
     */
    protected function formatTime(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds.' seconds';
        }

        if ($seconds < 3600) {
            $minutes = ceil($seconds / 60);

            return $minutes.' minute'.($minutes > 1 ? 's' : '');
        }

        $hours = ceil($seconds / 3600);

        return $hours.' hour'.($hours > 1 ? 's' : '');
    }

    /**
     * Get current usage for user.
     */
    public static function getCurrentUsage(int $userId, string $type): array
    {
        $usage = [];
        $periods = ['per_minute', 'per_hour', 'per_day'];

        foreach ($periods as $period) {
            $key = "rate_limit:{$type}:{$userId}:{$period}";
            $usage[$period] = RateLimiter::attempts($key);
        }

        return $usage;
    }

    /**
     * Clear rate limits for user (admin function).
     */
    public static function clearRateLimit(int $userId, ?string $type = null): void
    {
        $types = $type ? [$type] : ['email', 'sms', 'whatsapp', 'api', 'general'];
        $periods = ['per_minute', 'per_hour', 'per_day'];

        foreach ($types as $commType) {
            foreach ($periods as $period) {
                $key = "rate_limit:{$commType}:{$userId}:{$period}";
                RateLimiter::clear($key);
            }
        }
    }
}
