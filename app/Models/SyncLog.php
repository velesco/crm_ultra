<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'google_account_id',
        'user_id',
        'type',
        'status',
        'message',
        'details',
        'items_processed',
        'items_created',
        'items_updated',
        'items_failed',
        'started_at',
        'finished_at',
        'duration_seconds',
        'batch_id',
        'error_details',
    ];

    protected $casts = [
        'details' => 'array',
        'error_details' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * Get the Google account that owns the sync log.
     */
    public function googleAccount(): BelongsTo
    {
        return $this->belongsTo(GoogleAccount::class);
    }

    /**
     * Get the user that initiated the sync.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate and update duration.
     */
    public function calculateDuration(): void
    {
        if ($this->started_at && $this->finished_at) {
            $this->duration_seconds = $this->finished_at->diffInSeconds($this->started_at);
            $this->save();
        }
    }

    /**
     * Mark sync as started.
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'started',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark sync as successful.
     */
    public function markAsSuccess(string $message = null, array $details = null): void
    {
        $this->update([
            'status' => 'success',
            'message' => $message,
            'details' => $details,
            'finished_at' => now(),
        ]);
        
        $this->calculateDuration();
    }

    /**
     * Mark sync as failed.
     */
    public function markAsFailed(string $message, array $errorDetails = null): void
    {
        $this->update([
            'status' => 'failed',
            'message' => $message,
            'error_details' => $errorDetails,
            'finished_at' => now(),
        ]);
        
        $this->calculateDuration();
    }

    /**
     * Mark sync as partial (some items failed).
     */
    public function markAsPartial(string $message, array $details = null): void
    {
        $this->update([
            'status' => 'partial',
            'message' => $message,
            'details' => $details,
            'finished_at' => now(),
        ]);
        
        $this->calculateDuration();
    }

    /**
     * Update items processed counts.
     */
    public function updateCounts(int $processed = 0, int $created = 0, int $updated = 0, int $failed = 0): void
    {
        $this->update([
            'items_processed' => $this->items_processed + $processed,
            'items_created' => $this->items_created + $created,
            'items_updated' => $this->items_updated + $updated,
            'items_failed' => $this->items_failed + $failed,
        ]);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDuration(): string
    {
        if (!$this->duration_seconds) {
            return 'N/A';
        }

        if ($this->duration_seconds < 60) {
            return $this->duration_seconds . 's';
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        return $minutes . 'm ' . $seconds . 's';
    }

    /**
     * Get success rate percentage.
     */
    public function getSuccessRate(): float
    {
        if ($this->items_processed === 0) {
            return 100.0;
        }

        $successful = $this->items_processed - $this->items_failed;
        return round(($successful / $this->items_processed) * 100, 2);
    }

    /**
     * Check if sync is currently running.
     */
    public function isRunning(): bool
    {
        return $this->status === 'started' && !$this->finished_at;
    }

    /**
     * Check if sync was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if sync failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if sync was partial.
     */
    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    /**
     * Scope: Recent logs first.
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('started_at', 'desc');
    }

    /**
     * Scope: Successful syncs only.
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: Failed syncs only.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Running syncs only.
     */
    public function scopeRunning(Builder $query): Builder
    {
        return $query->where('status', 'started')
                    ->whereNull('finished_at');
    }

    /**
     * Scope: Syncs of specific type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Syncs in date range.
     */
    public function scopeInDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('started_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Syncs in same batch.
     */
    public function scopeInBatch(Builder $query, string $batchId): Builder
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Create a new sync log entry.
     */
    public static function createForSync(
        int $googleAccountId,
        int $userId,
        string $type,
        string $batchId = null
    ): self {
        return static::create([
            'google_account_id' => $googleAccountId,
            'user_id' => $userId,
            'type' => $type,
            'status' => 'started',
            'started_at' => now(),
            'batch_id' => $batchId ?: uniqid(),
            'items_processed' => 0,
            'items_created' => 0,
            'items_updated' => 0,
            'items_failed' => 0,
        ]);
    }
}
