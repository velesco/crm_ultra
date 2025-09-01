<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleSheetsSyncLog extends Model
{
    use HasFactory;

    protected $table = 'google_sheets_sync_logs';

    protected $fillable = [
        'integration_id',
        'created_by',
        'action', // import, export, sync
        'status', // success, failed, partial
        'records_processed',
        'records_success',
        'records_failed',
        'error_log',
        'message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'error_log' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function integration()
    {
        return $this->belongsTo(GoogleSheetsIntegration::class, 'integration_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Methods
    public function getDurationAttribute()
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    public function getSuccessRateAttribute()
    {
        if ($this->records_processed == 0) {
            return 0;
        }

        return round(($this->records_success / $this->records_processed) * 100, 2);
    }

    public function markAsStarted()
    {
        $this->update([
            'started_at' => now(),
        ]);
    }

    public function markAsSuccess($recordsProcessed = 0, $recordsSuccess = 0, $recordsFailed = 0)
    {
        $this->update([
            'status' => 'success',
            'records_processed' => $recordsProcessed,
            'records_success' => $recordsSuccess,
            'records_failed' => $recordsFailed,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed($errorMessage = null, $errorLog = null)
    {
        $this->update([
            'status' => 'failed',
            'message' => $errorMessage,
            'error_log' => $errorLog,
            'completed_at' => now(),
        ]);
    }

    public function markAsPartial($recordsProcessed = 0, $recordsSuccess = 0, $recordsFailed = 0, $message = null)
    {
        $this->update([
            'status' => 'partial',
            'records_processed' => $recordsProcessed,
            'records_success' => $recordsSuccess,
            'records_failed' => $recordsFailed,
            'message' => $message,
            'completed_at' => now(),
        ]);
    }
}
