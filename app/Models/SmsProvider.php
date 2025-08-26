<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'provider', // twilio, vonage, orange, custom
        'api_key',
        'api_secret',
        'sender_id',
        'webhook_url',
        'is_active',
        'daily_limit',
        'sent_today',
        'cost_per_sms',
        'settings',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'cost_per_sms' => 'decimal:4',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key',
        'api_secret'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function smsMessages()
    {
        return $this->hasMany(SmsMessage::class, 'provider_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('daily_limit')
                  ->orWhere('sent_today', '<', 'daily_limit');
            });
    }

    // Methods
    public function canSend()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->daily_limit && $this->sent_today >= $this->daily_limit) {
            return false;
        }

        return true;
    }

    public function incrementSent()
    {
        $this->increment('sent_today');
    }

    public function resetDailyCounter()
    {
        $this->update(['sent_today' => 0]);
    }

    public function testConnection()
    {
        // Implementation will be added in SMS service
        return app('sms.service')->testProvider($this);
    }

    public function sendSms($to, $message)
    {
        // Implementation will be added in SMS service
        return app('sms.service')->send($this, $to, $message);
    }
}
