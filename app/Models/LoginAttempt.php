<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'type',
        'metadata',
        'user_id',
        'blocked_until',
    ];

    protected $casts = [
        'metadata' => 'array',
        'blocked_until' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeFailed($query)
    {
        return $query->where('type', 'failed');
    }

    public function scopeSuccess($query)
    {
        return $query->where('type', 'success');
    }

    public function scopeBlocked($query)
    {
        return $query->where('type', 'blocked');
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeCurrentlyBlocked($query)
    {
        return $query->where('blocked_until', '>', now());
    }

    /**
     * Helper Methods
     */
    public static function recordAttempt(string $email, string $ip, string $type, array $metadata = [], ?int $userId = null)
    {
        return static::create([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'type' => $type,
            'metadata' => $metadata,
            'user_id' => $userId,
        ]);
    }

    public static function recordFailedAttempt(string $email, string $ip, array $metadata = [])
    {
        return static::recordAttempt($email, $ip, 'failed', $metadata);
    }

    public static function recordSuccessfulLogin(string $email, string $ip, int $userId, array $metadata = [])
    {
        return static::recordAttempt($email, $ip, 'success', $metadata, $userId);
    }

    public static function recordBlocked(string $email, string $ip, Carbon $blockedUntil, array $metadata = [])
    {
        return static::create([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'type' => 'blocked',
            'metadata' => $metadata,
            'blocked_until' => $blockedUntil,
        ]);
    }

    public static function getFailedAttemptsCount(string $identifier, string $type = 'email', int $hours = 1): int
    {
        $query = static::failed()->recent($hours);

        if ($type === 'email') {
            $query->byEmail($identifier);
        } else {
            $query->byIp($identifier);
        }

        return $query->count();
    }

    public static function isBlocked(string $identifier, string $type = 'email'): bool
    {
        $query = static::currentlyBlocked();

        if ($type === 'email') {
            $query->byEmail($identifier);
        } else {
            $query->byIp($identifier);
        }

        return $query->exists();
    }

    public static function getBlockedUntil(string $identifier, string $type = 'email'): ?Carbon
    {
        $query = static::currentlyBlocked();

        if ($type === 'email') {
            $query->byEmail($identifier);
        } else {
            $query->byIp($identifier);
        }

        $attempt = $query->first();

        return $attempt ? $attempt->blocked_until : null;
    }

    public static function clearOldAttempts(int $days = 30): int
    {
        return static::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Accessors
     */
    public function getLocationAttribute(): ?string
    {
        if (! $this->metadata || ! isset($this->metadata['country'])) {
            return null;
        }

        $location = $this->metadata['country'];
        if (isset($this->metadata['city'])) {
            $location = $this->metadata['city'].', '.$location;
        }

        return $location;
    }

    public function getDeviceAttribute(): ?string
    {
        if (! $this->metadata || ! isset($this->metadata['device'])) {
            return null;
        }

        return $this->metadata['device'];
    }

    public function getBrowserAttribute(): ?string
    {
        if (! $this->metadata || ! isset($this->metadata['browser'])) {
            return null;
        }

        return $this->metadata['browser'];
    }

    public function getIsBlockedAttribute(): bool
    {
        return $this->blocked_until && $this->blocked_until > now();
    }

    /**
     * Analytics Methods
     */
    public static function getSecurityStats(): array
    {
        $now = now();
        $today = $now->startOfDay();
        $yesterday = $now->copy()->subDay()->startOfDay();
        $thisWeek = $now->copy()->startOfWeek();
        $thisMonth = $now->copy()->startOfMonth();

        return [
            'total_attempts' => static::count(),
            'failed_attempts' => static::failed()->count(),
            'blocked_attempts' => static::blocked()->count(),
            'success_attempts' => static::success()->count(),

            'today' => [
                'total' => static::where('created_at', '>=', $today)->count(),
                'failed' => static::failed()->where('created_at', '>=', $today)->count(),
                'blocked' => static::blocked()->where('created_at', '>=', $today)->count(),
            ],

            'this_week' => [
                'total' => static::where('created_at', '>=', $thisWeek)->count(),
                'failed' => static::failed()->where('created_at', '>=', $thisWeek)->count(),
                'blocked' => static::blocked()->where('created_at', '>=', $thisWeek)->count(),
            ],

            'this_month' => [
                'total' => static::where('created_at', '>=', $thisMonth)->count(),
                'failed' => static::failed()->where('created_at', '>=', $thisMonth)->count(),
                'blocked' => static::blocked()->where('created_at', '>=', $thisMonth)->count(),
            ],

            'currently_blocked' => static::currentlyBlocked()->count(),
            'unique_ips_blocked' => static::currentlyBlocked()->distinct('ip_address')->count(),
        ];
    }
}
