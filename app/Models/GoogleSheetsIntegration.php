<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleSheetsIntegration extends Model
{
    use HasFactory;

    protected $table = 'google_sheets_integrations';

    protected $fillable = [
        'name',
        'spreadsheet_id',
        'sheet_name',
        'range',
        'access_token',
        'refresh_token',
        'sync_direction', // import, export, bidirectional
        'auto_sync',
        'sync_frequency', // hourly, daily, weekly
        'last_sync_at',
        'sync_status',
        'field_mapping',
        'settings',
        'created_by'
    ];

    protected $casts = [
        'auto_sync' => 'boolean',
        'last_sync_at' => 'datetime',
        'field_mapping' => 'array',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function syncLogs()
    {
        return $this->hasMany(GoogleSheetsSyncLog::class, 'integration_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('sync_status', 'active');
    }

    public function scopeAutoSync($query)
    {
        return $query->where('auto_sync', true);
    }

    // Methods
    public function sync()
    {
        // Implementation will be added in Google Sheets service
        return app('google.sheets.service')->sync($this);
    }

    public function testConnection()
    {
        // Implementation will be added in Google Sheets service
        return app('google.sheets.service')->testConnection($this);
    }

    public function shouldSync()
    {
        if (!$this->auto_sync) {
            return false;
        }

        if (!$this->last_sync_at) {
            return true;
        }

        $interval = match($this->sync_frequency) {
            'hourly' => 1,
            'daily' => 24,
            'weekly' => 168,
            default => 24
        };

        return $this->last_sync_at->addHours($interval)->isPast();
    }
}
