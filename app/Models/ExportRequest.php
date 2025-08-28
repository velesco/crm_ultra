<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'data_type',
        'format',
        'filters',
        'columns',
        'custom_query',
        'status',
        'file_path',
        'file_size',
        'progress',
        'status_message',
        'scheduled_for',
        'recurring_frequency',
        'is_public',
        'notify_on_completion',
        'include_attachments',
        'started_at',
        'completed_at',
        'error_message',
        'download_count',
        'user_id',
        'created_by',
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'is_public' => 'boolean',
        'notify_on_completion' => 'boolean',
        'include_attachments' => 'boolean',
        'scheduled_for' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'file_size' => 'integer',
        'progress' => 'integer',
        'download_count' => 'integer',
    ];

    /**
     * The user who owns this export
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The user who created this export
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for pending exports
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processing exports
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope for completed exports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed exports
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for scheduled exports
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_for')
            ->where('scheduled_for', '>', now());
    }

    /**
     * Scope for public exports
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for exports by data type
     */
    public function scopeByDataType($query, $type)
    {
        return $query->where('data_type', $type);
    }

    /**
     * Scope for exports by format
     */
    public function scopeByFormat($query, $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Scope for recent exports (last 30 days)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (! $this->file_size) {
            return null;
        }

        return formatBytes($this->file_size);
    }

    /**
     * Get status color for display
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'clock',
            'processing' => 'refresh',
            'completed' => 'check-circle',
            'failed' => 'x-circle',
            'cancelled' => 'x',
            default => 'help-circle'
        };
    }

    /**
     * Get formatted data type
     */
    public function getFormattedDataTypeAttribute(): string
    {
        return match ($this->data_type) {
            'contacts' => 'Contacts',
            'email_campaigns' => 'Email Campaigns',
            'sms_messages' => 'SMS Messages',
            'whatsapp_messages' => 'WhatsApp Messages',
            'revenue' => 'Revenue Data',
            'communications' => 'All Communications',
            'system_logs' => 'System Logs',
            'custom' => 'Custom Query',
            default => ucfirst(str_replace('_', ' ', $this->data_type))
        };
    }

    /**
     * Get formatted format type
     */
    public function getFormattedFormatAttribute(): string
    {
        return match ($this->format) {
            'csv' => 'CSV',
            'xlsx' => 'Excel (XLSX)',
            'json' => 'JSON',
            'pdf' => 'PDF Report',
            default => strtoupper($this->format)
        };
    }

    /**
     * Get processing duration
     */
    public function getProcessingDurationAttribute(): ?string
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        $duration = $this->started_at->diffInSeconds($this->completed_at);

        if ($duration < 60) {
            return "{$duration} seconds";
        } elseif ($duration < 3600) {
            $minutes = floor($duration / 60);
            $seconds = $duration % 60;

            return "{$minutes}m {$seconds}s";
        } else {
            $hours = floor($duration / 3600);
            $minutes = floor(($duration % 3600) / 60);

            return "{$hours}h {$minutes}m";
        }
    }

    /**
     * Get file name for download
     */
    public function getFileName(): string
    {
        $name = str_replace(' ', '_', $this->name);
        $timestamp = $this->created_at->format('Y-m-d_H-i-s');

        return "{$name}_{$timestamp}.{$this->format}";
    }

    /**
     * Check if export can be started
     */
    public function canStart(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if export can be cancelled
     */
    public function canCancel(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Check if export can be downloaded
     */
    public function canDownload(): bool
    {
        return $this->status === 'completed' && $this->file_path;
    }

    /**
     * Check if export is scheduled
     */
    public function isScheduled(): bool
    {
        return $this->scheduled_for && $this->scheduled_for->isFuture();
    }

    /**
     * Check if export is recurring
     */
    public function isRecurring(): bool
    {
        return ! is_null($this->recurring_frequency);
    }

    /**
     * Mark export as started
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
            'progress' => 0,
            'status_message' => 'Export processing started',
        ]);
    }

    /**
     * Update progress
     */
    public function updateProgress(int $progress, ?string $message = null): void
    {
        $this->update([
            'progress' => $progress,
            'status_message' => $message ?? "Processing... {$progress}%",
        ]);
    }

    /**
     * Mark export as completed
     */
    public function markAsCompleted(string $filePath, int $fileSize): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'progress' => 100,
            'status_message' => 'Export completed successfully',
        ]);
    }

    /**
     * Mark export as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $errorMessage,
            'status_message' => 'Export failed',
        ]);
    }

    /**
     * Get export statistics
     */
    public static function getStats(): array
    {
        return [
            'total' => self::count(),
            'pending' => self::where('status', 'pending')->count(),
            'processing' => self::where('status', 'processing')->count(),
            'completed' => self::where('status', 'completed')->count(),
            'failed' => self::where('status', 'failed')->count(),
            'cancelled' => self::where('status', 'cancelled')->count(),
            'scheduled' => self::scheduled()->count(),
            'total_size' => self::where('status', 'completed')->sum('file_size'),
            'total_downloads' => self::sum('download_count'),
        ];
    }

    /**
     * Get exports by data type
     */
    public static function getByDataType(): array
    {
        return self::selectRaw('data_type, COUNT(*) as count')
            ->groupBy('data_type')
            ->pluck('count', 'data_type')
            ->toArray();
    }

    /**
     * Get recent activity (last 7 days)
     */
    public static function getRecentActivity(): array
    {
        return self::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }

    /**
     * Clean up old exports
     */
    public static function cleanupOldExports(int $days = 30): int
    {
        $oldExports = self::where('created_at', '<', now()->subDays($days))
            ->where('status', 'completed')
            ->get();

        $deleted = 0;
        foreach ($oldExports as $export) {
            if ($export->file_path && \Storage::exists($export->file_path)) {
                \Storage::delete($export->file_path);
            }
            $export->delete();
            $deleted++;
        }

        return $deleted;
    }
}
