<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'file_type',
        'status',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'column_mapping',
        'validation_rules',
        'error_log',
        'preview_data',
        'created_by'
    ];

    protected $casts = [
        'column_mapping' => 'array',
        'validation_rules' => 'array',
        'error_log' => 'array',
        'preview_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Methods
    public function getProgressAttribute()
    {
        if ($this->total_rows == 0) {
            return 0;
        }
        
        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }

    public function getSuccessRateAttribute()
    {
        if ($this->processed_rows == 0) {
            return 0;
        }
        
        return round(($this->successful_rows / $this->processed_rows) * 100, 2);
    }

    public function addError($row, $field, $message)
    {
        $errorLog = $this->error_log ?? [];
        $errorLog[] = [
            'row' => $row,
            'field' => $field,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];
        $this->error_log = $errorLog;
        $this->save();
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'processed_rows' => $this->total_rows
        ]);
    }

    public function markAsFailed($error = null)
    {
        $this->update([
            'status' => 'failed',
            'error_log' => $error ? [$error] : $this->error_log
        ]);
    }
}
