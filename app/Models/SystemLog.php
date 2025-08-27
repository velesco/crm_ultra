<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'level',
        'category',
        'created_at'
    ];

    protected $casts = [
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    /**
     * Get the user that performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by level
     */
    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for filtering by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted metadata
     */
    public function getFormattedMetadataAttribute(): string
    {
        if (is_array($this->metadata)) {
            return json_encode($this->metadata, JSON_PRETTY_PRINT);
        }
        
        return $this->metadata ?? '';
    }

    /**
     * Get log level color for UI
     */
    public function getLevelColorAttribute(): string
    {
        return match($this->level) {
            'error' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
            'debug' => 'secondary',
            default => 'primary'
        };
    }

    /**
     * Get action icon for UI
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'user_login' => 'log-in',
            'user_logout' => 'log-out',
            'user_created' => 'user-plus',
            'user_updated' => 'user-check',
            'user_deleted' => 'user-minus',
            'campaign_sent' => 'mail',
            'data_import' => 'download',
            'data_export' => 'upload',
            'system_backup' => 'database',
            'maintenance_toggle' => 'tool',
            'cache_clear' => 'trash-2',
            'system_optimize' => 'zap',
            default => 'activity'
        };
    }

    /**
     * Create a system log entry
     */
    public static function createLog(
        int $userId,
        string $action,
        string $description,
        array $metadata = [],
        string $level = 'info',
        string $category = 'system'
    ): self {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'level' => $level,
            'category' => $category
        ]);
    }
}
