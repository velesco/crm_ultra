<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class GoogleAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'email',
        'provider',
        'scopes',
        'access_token_encrypted',
        'refresh_token_encrypted',
        'token_expires_at',
        'visibility',
        'status',
        'last_sync_at',
        'sync_settings',
        'auto_sync_enabled',
        'sync_frequency_minutes',
    ];

    protected $casts = [
        'scopes' => 'array',
        'sync_settings' => 'array',
        'token_expires_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'auto_sync_enabled' => 'boolean',
    ];

    protected $hidden = [
        'access_token_encrypted',
        'refresh_token_encrypted',
    ];

    /**
     * Get the user that owns the Google account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team that owns the Google account.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get all emails for this account.
     */
    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    /**
     * Get all sync logs for this account.
     */
    public function syncLogs(): HasMany
    {
        return $this->hasMany(SyncLog::class);
    }

    /**
     * Get decrypted access token.
     */
    public function getAccessTokenAttribute(): ?string
    {
        return $this->access_token_encrypted ? Crypt::decryptString($this->access_token_encrypted) : null;
    }

    /**
     * Set encrypted access token.
     */
    public function setAccessTokenAttribute(?string $value): void
    {
        $this->attributes['access_token_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get decrypted refresh token.
     */
    public function getRefreshTokenAttribute(): ?string
    {
        return $this->refresh_token_encrypted ? Crypt::decryptString($this->refresh_token_encrypted) : null;
    }

    /**
     * Set encrypted refresh token.
     */
    public function setRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['refresh_token_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Check if token is expired.
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    /**
     * Check if account needs sync.
     */
    public function needsSync(): bool
    {
        if (!$this->auto_sync_enabled || $this->status !== 'active') {
            return false;
        }

        if (!$this->last_sync_at) {
            return true;
        }

        $nextSyncAt = $this->last_sync_at->addMinutes($this->sync_frequency_minutes);
        return now()->gte($nextSyncAt);
    }

    /**
     * Check if account has specific scope.
     */
    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes ?? []);
    }

    /**
     * Scope: Active accounts only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Accounts visible to team.
     */
    public function scopeTeamVisible($query)
    {
        return $query->where('visibility', 'team');
    }

    /**
     * Scope: Accounts for a specific team.
     */
    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Get recent sync logs.
     */
    public function recentSyncLogs($limit = 10)
    {
        return $this->syncLogs()
                    ->orderBy('started_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get unread email count.
     */
    public function getUnreadEmailsCount(): int
    {
        return $this->emails()->where('is_read', false)->count();
    }
}
