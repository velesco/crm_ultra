<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'webhook_type',
        'provider',
        'event_type',
        'status',
        'method',
        'url',
        'headers',
        'payload',
        'processed_data',
        'attempts',
        'response',
        'response_code',
        'error_message',
        'error_context',
        'webhook_id',
        'reference_id',
        'reference_type',
        'ip_address',
        'user_agent',
        'webhook_received_at',
        'processed_at',
        'next_retry_at',
        'metadata',
    ];

    protected $casts = [
        'headers' => 'array',
        'processed_data' => 'array',
        'error_context' => 'array',
        'metadata' => 'array',
        'webhook_received_at' => 'datetime',
        'processed_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Webhook types constants
    const TYPE_EMAIL = 'email';

    const TYPE_SMS = 'sms';

    const TYPE_WHATSAPP = 'whatsapp';

    const TYPE_GOOGLE_SHEETS = 'google_sheets';

    const TYPE_API = 'api';

    // Status constants
    const STATUS_PENDING = 'pending';

    const STATUS_PROCESSING = 'processing';

    const STATUS_COMPLETED = 'completed';

    const STATUS_FAILED = 'failed';

    const STATUS_RETRYING = 'retrying';

    // Provider constants
    const PROVIDER_SENDGRID = 'sendgrid';

    const PROVIDER_MAILGUN = 'mailgun';

    const PROVIDER_SES = 'ses';

    const PROVIDER_TWILIO = 'twilio';

    const PROVIDER_NEXMO = 'nexmo';

    const PROVIDER_WHATSAPP = 'whatsapp';

    const PROVIDER_GOOGLE = 'google';

    // Event types constants
    const EVENT_DELIVERED = 'delivered';

    const EVENT_BOUNCED = 'bounced';

    const EVENT_OPENED = 'opened';

    const EVENT_CLICKED = 'clicked';

    const EVENT_FAILED = 'failed';

    const EVENT_SPAM = 'spam';

    const EVENT_UNSUBSCRIBE = 'unsubscribe';

    /**
     * Scope for filtering by webhook type
     */
    public function scopeType($query, $type)
    {
        return $query->where('webhook_type', $type);
    }

    /**
     * Scope for filtering by provider
     */
    public function scopeProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by event type
     */
    public function scopeEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope for failed webhooks
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for pending webhooks
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for webhooks ready for retry
     */
    public function scopeReadyForRetry($query)
    {
        return $query->where('status', self::STATUS_FAILED)
            ->where('next_retry_at', '<=', now())
            ->where('attempts', '<', 5); // Max 5 retry attempts
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('webhook_received_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);
    }

    /**
     * Scope for recent webhooks
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('webhook_received_at', '>=', Carbon::now()->subHours($hours));
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('webhook_type', 'like', "%{$search}%")
                ->orWhere('provider', 'like', "%{$search}%")
                ->orWhere('event_type', 'like', "%{$search}%")
                ->orWhere('webhook_id', 'like', "%{$search}%")
                ->orWhere('reference_id', 'like', "%{$search}%")
                ->orWhere('error_message', 'like', "%{$search}%");
        });
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_PROCESSING => 'badge-info',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_FAILED => 'badge-danger',
            self::STATUS_RETRYING => 'badge-secondary',
            default => 'badge-light'
        };
    }

    /**
     * Get webhook type icon
     */
    public function getTypeIconAttribute()
    {
        return match ($this->webhook_type) {
            self::TYPE_EMAIL => 'fas fa-envelope',
            self::TYPE_SMS => 'fas fa-sms',
            self::TYPE_WHATSAPP => 'fab fa-whatsapp',
            self::TYPE_GOOGLE_SHEETS => 'fab fa-google',
            self::TYPE_API => 'fas fa-code',
            default => 'fas fa-webhook'
        };
    }

    /**
     * Get provider icon
     */
    public function getProviderIconAttribute()
    {
        return match ($this->provider) {
            self::PROVIDER_SENDGRID => 'fas fa-paper-plane',
            self::PROVIDER_MAILGUN => 'fas fa-mail-bulk',
            self::PROVIDER_SES => 'fab fa-aws',
            self::PROVIDER_TWILIO => 'fas fa-phone',
            self::PROVIDER_NEXMO => 'fas fa-mobile-alt',
            self::PROVIDER_WHATSAPP => 'fab fa-whatsapp',
            self::PROVIDER_GOOGLE => 'fab fa-google',
            default => 'fas fa-server'
        };
    }

    /**
     * Get event type icon
     */
    public function getEventTypeIconAttribute()
    {
        return match ($this->event_type) {
            self::EVENT_DELIVERED => 'fas fa-check-circle text-success',
            self::EVENT_BOUNCED => 'fas fa-exclamation-triangle text-warning',
            self::EVENT_OPENED => 'fas fa-envelope-open text-info',
            self::EVENT_CLICKED => 'fas fa-mouse-pointer text-primary',
            self::EVENT_FAILED => 'fas fa-times-circle text-danger',
            self::EVENT_SPAM => 'fas fa-ban text-danger',
            self::EVENT_UNSUBSCRIBE => 'fas fa-user-minus text-muted',
            default => 'fas fa-info-circle'
        };
    }

    /**
     * Get formatted processing time
     */
    public function getProcessingTimeAttribute()
    {
        if (! $this->processed_at || ! $this->webhook_received_at) {
            return null;
        }

        return $this->processed_at->diffInMilliseconds($this->webhook_received_at);
    }

    /**
     * Check if webhook can be retried
     */
    public function canRetry()
    {
        return $this->status === self::STATUS_FAILED &&
               $this->attempts < 5 &&
               (! $this->next_retry_at || $this->next_retry_at <= now());
    }

    /**
     * Mark webhook as processing
     */
    public function markAsProcessing()
    {
        return $this->update([
            'status' => self::STATUS_PROCESSING,
            'attempts' => $this->attempts + 1,
        ]);
    }

    /**
     * Mark webhook as completed
     */
    public function markAsCompleted($processedData = null, $response = null)
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_data' => $processedData,
            'response' => $response,
            'processed_at' => now(),
            'error_message' => null,
            'error_context' => null,
        ]);
    }

    /**
     * Mark webhook as failed
     */
    public function markAsFailed($errorMessage, $errorContext = null, $retryAfter = null)
    {
        $nextRetryAt = null;
        if ($this->attempts < 5 && $retryAfter !== false) {
            // Exponential backoff: 1min, 5min, 15min, 1hr, 6hr
            $delays = [1, 5, 15, 60, 360];
            $delay = $delays[min($this->attempts, 4)];
            $nextRetryAt = now()->addMinutes($delay);
        }

        return $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'error_context' => $errorContext,
            'next_retry_at' => $nextRetryAt,
        ]);
    }

    /**
     * Create a new webhook log entry
     */
    public static function createLog(
        string $webhookType,
        string $provider,
        string $eventType,
        string $url,
        string $payload,
        ?array $headers = null,
        ?string $webhookId = null,
        ?array $metadata = null
    ) {
        return self::create([
            'webhook_type' => $webhookType,
            'provider' => $provider,
            'event_type' => $eventType,
            'status' => self::STATUS_PENDING,
            'method' => 'POST',
            'url' => $url,
            'headers' => $headers,
            'payload' => $payload,
            'webhook_id' => $webhookId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'webhook_received_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get statistics
     */
    public static function getStatistics()
    {
        $totalWebhooks = self::count();
        $todayWebhooks = self::whereDate('created_at', today())->count();
        $failedWebhooks = self::status(self::STATUS_FAILED)->count();
        $pendingWebhooks = self::status(self::STATUS_PENDING)->count();
        $completedWebhooks = self::status(self::STATUS_COMPLETED)->count();

        return [
            'total_webhooks' => $totalWebhooks,
            'today_webhooks' => $todayWebhooks,
            'completed_webhooks' => $completedWebhooks,
            'failed_webhooks' => $failedWebhooks,
            'pending_webhooks' => $pendingWebhooks,
            'success_rate' => $totalWebhooks > 0 ? round(($completedWebhooks / $totalWebhooks) * 100, 2) : 100,
            'avg_processing_time' => self::whereNotNull('processed_at')
                ->whereNotNull('webhook_received_at')
                ->get()
                ->avg(function ($log) {
                    return $log->processing_time;
                }),
        ];
    }

    /**
     * Get webhook statistics by provider
     */
    public static function getProviderStatistics()
    {
        return self::select('provider')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN status = ? THEN 1 END) as completed', [self::STATUS_COMPLETED])
            ->selectRaw('COUNT(CASE WHEN status = ? THEN 1 END) as failed', [self::STATUS_FAILED])
            ->selectRaw('COUNT(CASE WHEN status = ? THEN 1 END) as pending', [self::STATUS_PENDING])
            ->groupBy('provider')
            ->get();
    }

    /**
     * Get webhook statistics by type
     */
    public static function getTypeStatistics()
    {
        return self::select('webhook_type')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN status = ? THEN 1 END) as completed', [self::STATUS_COMPLETED])
            ->selectRaw('COUNT(CASE WHEN status = ? THEN 1 END) as failed', [self::STATUS_FAILED])
            ->selectRaw('COUNT(CASE WHEN status = ? THEN 1 END) as pending', [self::STATUS_PENDING])
            ->groupBy('webhook_type')
            ->get();
    }
}
