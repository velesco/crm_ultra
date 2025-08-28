<?php

namespace App\Http\Middleware;

use App\Models\WhatsAppSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateWhatsAppSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $sessionId = null): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->handleUnauthenticated($request);
        }

        // Get WhatsApp session from parameter or request
        $session = $this->getWhatsAppSession($request, $sessionId);

        if (! $session) {
            return $this->handleMissingSession($request);
        }

        // Check if user owns or has access to this session
        if (! $this->userCanUseSession($user, $session)) {
            return $this->handleUnauthorizedSession($request, $session);
        }

        // Validate session status and health
        $validationChecks = [
            'status' => $this->checkSessionStatus($session),
            'health' => $this->checkSessionHealth($session),
            'limits' => $this->checkSessionLimits($session),
            'connection' => $this->checkConnectionStability($session),
            'webhook' => $this->checkWebhookHealth($session),
        ];

        foreach ($validationChecks as $type => $check) {
            if (! $check['valid']) {
                return $this->handleValidationFailure($request, $type, $check, $session);
            }
        }

        // Store session in request for controllers to use
        $request->attributes->set('whatsapp_session', $session);

        // Update session last used timestamp
        $session->touch('last_used_at');

        return $next($request);
    }

    /**
     * Get WhatsApp session from request or parameter.
     */
    protected function getWhatsAppSession(Request $request, ?string $sessionId): ?WhatsAppSession
    {
        // Try to get from route parameter
        if ($sessionId) {
            return WhatsAppSession::find($sessionId);
        }

        // Try to get from request data
        if ($request->has('whatsapp_session_id')) {
            return WhatsAppSession::find($request->input('whatsapp_session_id'));
        }

        // Try to get from route model binding
        if ($request->route('whatsappSession') || $request->route('session')) {
            return $request->route('whatsappSession') ?? $request->route('session');
        }

        // Get user's default active session
        $user = $request->user();

        return $user->whatsappSessions()->connected()->first();
    }

    /**
     * Check if user can use this WhatsApp session.
     */
    protected function userCanUseSession($user, WhatsAppSession $session): bool
    {
        // Admin can use any session
        if ($user->hasRole('admin')) {
            return true;
        }

        // User owns this session
        if ($session->created_by === $user->id) {
            return true;
        }

        // Managers can use sessions from their team
        if ($user->hasRole('manager')) {
            return $session->creator->hasRole(['agent']) ||
                   $session->is_shared;
        }

        // Check if session is shared with user
        return $session->is_shared && $session->shared_with_users->contains($user->id);
    }

    /**
     * Check session status.
     */
    protected function checkSessionStatus(WhatsAppSession $session): array
    {
        $validStatuses = ['connected', 'connecting'];

        return [
            'valid' => in_array($session->status, $validStatuses),
            'current_status' => $session->status,
            'message' => $session->status === 'connected'
                ? 'Session is connected and ready'
                : ($session->status === 'connecting'
                    ? 'Session is connecting, please wait'
                    : "Session is {$session->status}"),
        ];
    }

    /**
     * Check session health.
     */
    protected function checkSessionHealth(WhatsAppSession $session): array
    {
        $isHealthy = true;
        $issues = [];

        // Check if session has been inactive for too long
        if ($session->last_used_at && $session->last_used_at->diffInHours(now()) > 24) {
            $isHealthy = false;
            $issues[] = 'Session inactive for more than 24 hours';
        }

        // Check error rate
        $errorRate = $session->getErrorRateForPeriod('hour');
        if ($errorRate > 20) {
            $isHealthy = false;
            $issues[] = "High error rate: {$errorRate}%";
        }

        // Check if marked as unhealthy
        if ($session->is_healthy === false) {
            $isHealthy = false;
            $issues[] = 'Session marked as unhealthy';
        }

        // Check webhook connectivity
        if (! $this->checkWebhookConnectivity($session)) {
            $isHealthy = false;
            $issues[] = 'Webhook connectivity issues';
        }

        return [
            'valid' => $isHealthy,
            'issues' => $issues,
            'error_rate' => $errorRate ?? 0,
        ];
    }

    /**
     * Check session limits.
     */
    protected function checkSessionLimits(WhatsAppSession $session): array
    {
        $limits = $this->getSessionLimits($session);
        $usage = $this->getSessionUsage($session);

        $limitExceeded = false;
        $exceededLimits = [];

        foreach ($limits as $type => $limit) {
            if ($usage[$type] >= $limit) {
                $limitExceeded = true;
                $exceededLimits[] = $type;
            }
        }

        return [
            'valid' => ! $limitExceeded,
            'limits' => $limits,
            'usage' => $usage,
            'exceeded' => $exceededLimits,
        ];
    }

    /**
     * Check connection stability.
     */
    protected function checkConnectionStability(WhatsAppSession $session): array
    {
        // Check recent disconnections
        $recentDisconnections = $session->getLogs()
            ->where('type', 'disconnection')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        $isStable = $recentDisconnections < 3;

        // Check message delivery success rate
        $deliveryRate = $session->getDeliveryRateForPeriod('hour');
        if ($deliveryRate < 80) {
            $isStable = false;
        }

        return [
            'valid' => $isStable,
            'recent_disconnections' => $recentDisconnections,
            'delivery_rate' => $deliveryRate,
        ];
    }

    /**
     * Check webhook health.
     */
    protected function checkWebhookHealth(WhatsAppSession $session): array
    {
        $webhookUrl = $session->webhook_url;

        if (! $webhookUrl) {
            return [
                'valid' => true, // Webhook is optional
                'configured' => false,
            ];
        }

        // Check webhook response time
        $lastWebhookResponse = $session->last_webhook_response_at;
        $webhookHealthy = true;

        if ($lastWebhookResponse && $lastWebhookResponse->diffInMinutes(now()) > 30) {
            $webhookHealthy = false;
        }

        return [
            'valid' => $webhookHealthy,
            'configured' => true,
            'url' => $webhookUrl,
            'last_response' => $lastWebhookResponse,
        ];
    }

    /**
     * Check webhook connectivity.
     */
    protected function checkWebhookConnectivity(WhatsAppSession $session): bool
    {
        if (! $session->webhook_url) {
            return true; // No webhook configured, so no connectivity issues
        }

        try {
            $response = \Http::timeout(5)->get($session->webhook_url.'/health');

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get session limits based on plan and configuration.
     */
    protected function getSessionLimits(WhatsAppSession $session): array
    {
        $user = $session->creator;
        $plan = $user->subscription_plan ?? 'free';

        return match ($plan) {
            'free' => [
                'messages_per_hour' => 20,
                'messages_per_day' => 100,
                'media_messages_per_day' => 10,
            ],
            'basic' => [
                'messages_per_hour' => 100,
                'messages_per_day' => 500,
                'media_messages_per_day' => 50,
            ],
            'pro' => [
                'messages_per_hour' => 500,
                'messages_per_day' => 3000,
                'media_messages_per_day' => 300,
            ],
            'enterprise' => [
                'messages_per_hour' => 2000,
                'messages_per_day' => 20000,
                'media_messages_per_day' => 2000,
            ],
            default => $this->getSessionLimits($session) // Default to free
        };
    }

    /**
     * Get current session usage.
     */
    protected function getSessionUsage(WhatsAppSession $session): array
    {
        return [
            'messages_per_hour' => $session->getMessageCountForPeriod('hour'),
            'messages_per_day' => $session->getMessageCountForPeriod('day'),
            'media_messages_per_day' => $session->getMediaMessageCountForPeriod('day'),
        ];
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
     * Handle missing session.
     */
    protected function handleMissingSession(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'WhatsApp session required',
                'message' => 'Please set up a WhatsApp session first.',
            ], 422);
        }

        return redirect()->route('whatsapp.sessions.create')
            ->with('error', 'Please set up a WhatsApp session first.');
    }

    /**
     * Handle unauthorized session.
     */
    protected function handleUnauthorizedSession(Request $request, WhatsAppSession $session): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthorized WhatsApp session',
                'message' => 'You do not have permission to use this WhatsApp session.',
            ], 403);
        }

        return redirect()->route('whatsapp.sessions.index')
            ->with('error', 'You do not have permission to use this WhatsApp session.');
    }

    /**
     * Handle validation failure.
     */
    protected function handleValidationFailure(Request $request, string $type, array $check, WhatsAppSession $session): Response
    {
        $message = $this->getValidationFailureMessage($type, $check, $session);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'WhatsApp session validation failed',
                'type' => $type,
                'message' => $message,
                'details' => $check,
            ], match ($type) {
                'status' => 503,
                'health' => 503,
                'limits' => 429,
                'connection' => 503,
                'webhook' => 503,
                default => 422
            });
        }

        $redirectRoute = match ($type) {
            'status' => 'whatsapp.sessions.show',
            'health', 'connection' => 'whatsapp.sessions.index',
            'limits' => 'dashboard',
            'webhook' => 'whatsapp.sessions.edit',
            default => 'whatsapp.sessions.index'
        };

        return redirect()->route($redirectRoute, ['session' => $session->id])
            ->with('error', $message);
    }

    /**
     * Get validation failure message.
     */
    protected function getValidationFailureMessage(string $type, array $check, WhatsAppSession $session): string
    {
        return match ($type) {
            'status' => "WhatsApp session is {$check['current_status']}. ".$check['message'],
            'health' => 'WhatsApp session is unhealthy: '.implode(', ', $check['issues']),
            'limits' => 'WhatsApp session limits exceeded: '.implode(', ', $check['exceeded']),
            'connection' => 'WhatsApp session connection is unstable. Please check your connection.',
            'webhook' => 'WhatsApp session webhook is not responding properly.',
            default => 'WhatsApp session validation failed.'
        };
    }

    /**
     * Get session status for API response.
     */
    public static function getSessionStatus(WhatsAppSession $session): array
    {
        $middleware = new self;

        return [
            'id' => $session->id,
            'name' => $session->name,
            'status' => $session->status,
            'is_healthy' => $session->is_healthy,
            'last_used_at' => $session->last_used_at,
            'validation' => [
                'status' => $middleware->checkSessionStatus($session),
                'health' => $middleware->checkSessionHealth($session),
                'limits' => $middleware->checkSessionLimits($session),
                'connection' => $middleware->checkConnectionStability($session),
                'webhook' => $middleware->checkWebhookHealth($session),
            ],
        ];
    }
}
