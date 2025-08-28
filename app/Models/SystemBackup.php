<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemBackup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'file_path',
        'file_size',
        'status',
        'error_message',
        'created_by',
        'started_at',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
        'file_size' => 'integer',
    ];

    /**
     * Relationship with User who created the backup
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for successful backups
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed backups
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for in progress backups
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for backup type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        if (! $this->file_size) {
            return 'N/A';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Get backup duration
     */
    public function getDurationAttribute()
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        $duration = $this->duration;

        if (! $duration) {
            return 'N/A';
        }

        if ($duration < 60) {
            return $duration.'s';
        }

        $minutes = floor($duration / 60);
        $seconds = $duration % 60;

        return $minutes.'m '.$seconds.'s';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'completed' => 'success',
            'failed' => 'danger',
            'in_progress' => 'warning',
            'restoring' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute()
    {
        return match ($this->status) {
            'completed' => 'fas fa-check-circle',
            'failed' => 'fas fa-times-circle',
            'in_progress' => 'fas fa-spinner fa-spin',
            'restoring' => 'fas fa-undo',
            default => 'fas fa-question-circle'
        };
    }

    /**
     * Get backup type icon
     */
    public function getTypeIconAttribute()
    {
        return match ($this->type) {
            'full' => 'fas fa-database',
            'database' => 'fas fa-table',
            'files' => 'fas fa-folder',
            'scheduled' => 'fas fa-clock',
            default => 'fas fa-archive'
        };
    }

    /**
     * Check if backup can be restored
     */
    public function canBeRestored()
    {
        return $this->status === 'completed' && ! empty($this->file_path);
    }

    /**
     * Check if backup can be deleted
     */
    public function canBeDeleted()
    {
        return ! in_array($this->status, ['in_progress', 'restoring']);
    }

    /**
     * Get backup age in days
     */
    public function getAgeInDaysAttribute()
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Check if backup is recent (less than 7 days old)
     */
    public function isRecent()
    {
        return $this->age_in_days <= 7;
    }

    /**
     * Check if backup is old (more than 30 days old)
     */
    public function isOld()
    {
        return $this->age_in_days > 30;
    }

    /**
     * Get backup health status
     */
    public function getHealthStatusAttribute()
    {
        if ($this->status === 'failed') {
            return 'unhealthy';
        }

        if ($this->status === 'completed') {
            if ($this->isOld()) {
                return 'warning';
            }

            return 'healthy';
        }

        return 'unknown';
    }

    /**
     * Get download URL
     */
    public function getDownloadUrlAttribute()
    {
        if ($this->status !== 'completed' || ! $this->file_path) {
            return null;
        }

        return route('admin.backups.download', $this);
    }

    /**
     * Static method to get backup statistics
     */
    public static function getStatistics()
    {
        return [
            'total' => self::count(),
            'completed' => self::completed()->count(),
            'failed' => self::failed()->count(),
            'in_progress' => self::inProgress()->count(),
            'total_size' => self::completed()->sum('file_size'),
            'avg_duration' => self::completed()->whereNotNull('started_at')->whereNotNull('completed_at')->get()->avg('duration'),
            'last_backup' => self::completed()->latest()->first(),
            'oldest_backup' => self::completed()->oldest()->first(),
        ];
    }

    /**
     * Static method to cleanup old backups
     */
    public static function cleanupOld($daysToKeep = 30)
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);

        return self::where('created_at', '<', $cutoffDate)
            ->where('status', 'completed')
            ->delete();
    }

    /**
     * Static method to get recent backup activity
     */
    public static function getRecentActivity($limit = 10)
    {
        return self::with('creator')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Check if file exists on disk
     */
    public function fileExists()
    {
        if (! $this->file_path) {
            return false;
        }

        return file_exists(storage_path('app/'.$this->file_path));
    }

    /**
     * Get actual file size from disk
     */
    public function getActualFileSize()
    {
        if (! $this->fileExists()) {
            return null;
        }

        return filesize(storage_path('app/'.$this->file_path));
    }

    /**
     * Validate backup integrity
     */
    public function validateIntegrity()
    {
        if (! $this->fileExists()) {
            return ['valid' => false, 'error' => 'File not found'];
        }

        $actualSize = $this->getActualFileSize();

        if ($actualSize !== $this->file_size) {
            return ['valid' => false, 'error' => 'File size mismatch'];
        }

        // Additional ZIP validation could be added here

        return ['valid' => true, 'message' => 'Backup is valid'];
    }
}
