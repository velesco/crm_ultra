<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppSession extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_sessions';

    protected $fillable = [
        'session_name',
        'phone_number',
        'status',
        'qr_code',
        'webhook_url',
        'api_endpoint',
        'api_key',
        'is_active',
        'last_activity',
        'settings',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_activity' => 'datetime',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages()
    {
        return $this->hasMany(WhatsAppMessage::class, 'session_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeConnected($query)
    {
        return $query->where('status', 'connected');
    }

    // Methods
    public function isConnected()
    {
        return $this->status === 'connected';
    }

    public function updateStatus($status, $qrCode = null)
    {
        $this->update([
            'status' => $status,
            'qr_code' => $qrCode,
            'last_activity' => now()
        ]);
    }

    public function sendMessage($to, $message, $type = 'text', $media = null)
    {
        // Implementation will be added in WhatsApp service
        return app('whatsapp.service')->sendMessage($this, $to, $message, $type, $media);
    }
}
