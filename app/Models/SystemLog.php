<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SystemLog extends Model
{
    protected $fillable = [
        'user_id',
        'level',
        'category',
        'action',
        'message',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'session_id',
        'request_id',
        'context',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'context' => 'array',
        'occurred_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Log levels constants
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';

    // Categories constants
    const CATEGORY_AUTHENTICATION = 'authentication';
    const CATEGORY_EMAIL = 'email';
    const CATEGORY_SMS = 'sms';
    const CATEGORY_WHATSAPP = 'whatsapp';
    const CATEGORY_SYSTEM = 'system';
    const CATEGORY_CONTACT = 'contact';
    const CATEGORY_CAMPAIGN = 'campaign';
    const CATEGORY_API = 'api';

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by level
     */
    public function scopeLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for filtering by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for filtering by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('occurred_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('occurred_at', '>=', Carbon::now()->subHours($hours));
    }

    /**
     * Scope for errors
     */
    public function scopeErrors($query)
    {
        return $query->whereIn('level', [self::LEVEL_ERROR, self::LEVEL_CRITICAL]);
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('message', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('action', 'like', "%{$search}%")
              ->orWhereHas('user', function($userQuery) use ($search) {
                  $userQuery->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Get log level badge class
     */
    public function getLevelBadgeClassAttribute()
    {
        return match($this->level) {
            self::LEVEL_DEBUG => 'badge-secondary',
            self::LEVEL_INFO => 'badge-primary',
            self::LEVEL_WARNING => 'badge-warning',
            self::LEVEL_ERROR => 'badge-danger',
            self::LEVEL_CRITICAL => 'badge-dark',
            default => 'badge-light'
        };
    }

    /**
     * Get category icon
     */
    public function getCategoryIconAttribute()
    {
        return match($this->category) {
            self::CATEGORY_AUTHENTICATION => 'fas fa-shield-alt',
            self::CATEGORY_EMAIL => 'fas fa-envelope',
            self::CATEGORY_SMS => 'fas fa-sms',
            self::CATEGORY_WHATSAPP => 'fab fa-whatsapp',
            self::CATEGORY_SYSTEM => 'fas fa-cogs',
            self::CATEGORY_CONTACT => 'fas fa-users',
            self::CATEGORY_CAMPAIGN => 'fas fa-bullhorn',
            self::CATEGORY_API => 'fas fa-code',
            default => 'fas fa-info-circle'
        };
    }

    /**
     * Create a new log entry
     */
    public static function createLog(
        string $level,
        string $category,
        string $action,
        string $message,
        ?string $description = null,
        ?array $metadata = null,
        ?int $userId = null
    ) {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'level' => $level,
            'category' => $category,
            'action' => $action,
            'message' => $message,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'request_id' => request()->header('X-Request-ID') ?? uniqid(),
            'context' => [
                'route' => request()->route()?->getName(),
                'method' => request()->method(),
                'url' => request()->url(),
            ],
            'occurred_at' => now(),
        ]);
    }

    /**
     * Helper methods for common log types
     */
    public static function info(string $category, string $action, string $message, ?array $metadata = null)
    {
        return self::createLog(self::LEVEL_INFO, $category, $action, $message, null, $metadata);
    }

    public static function warning(string $category, string $action, string $message, ?array $metadata = null)
    {
        return self::createLog(self::LEVEL_WARNING, $category, $action, $message, null, $metadata);
    }

    public static function error(string $category, string $action, string $message, ?array $metadata = null)
    {
        return self::createLog(self::LEVEL_ERROR, $category, $action, $message, null, $metadata);
    }

    public static function critical(string $category, string $action, string $message, ?array $metadata = null)
    {
        return self::createLog(self::LEVEL_CRITICAL, $category, $action, $message, null, $metadata);
    }

    /**
     * Get statistics
     */
    public static function getStatistics()
    {
        $totalLogs = self::count();
        $todayLogs = self::whereDate('created_at', today())->count();
        $errorLogs = self::errors()->count();
        $warningLogs = self::level(self::LEVEL_WARNING)->count();

        return [
            'total_logs' => $totalLogs,
            'today_logs' => $todayLogs,
            'error_logs' => $errorLogs,
            'warning_logs' => $warningLogs,
            'success_rate' => $totalLogs > 0 ? round((($totalLogs - $errorLogs) / $totalLogs) * 100, 2) : 100,
        ];
    }
}
