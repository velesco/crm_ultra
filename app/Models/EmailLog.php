<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'contact_id',
        'smtp_config_id',
        'subject',
        'content',
        'to_email',
        'status',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'bounced_at',
        'error_message',
        'tracking_id',
        'user_agent',
        'ip_address',
        'metadata'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'bounced_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function campaign()
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function smtpConfig()
    {
        return $this->belongsTo(SmtpConfig::class);
    }

    // Scopes
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeDelivered($query)
    {
        return $query->whereNotNull('delivered_at');
    }

    public function scopeOpened($query)
    {
        return $query->whereNotNull('opened_at');
    }

    public function scopeClicked($query)
    {
        return $query->whereNotNull('clicked_at');
    }

    public function scopeBounced($query)
    {
        return $query->whereNotNull('bounced_at');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Methods
    public function markAsDelivered()
    {
        if (!$this->delivered_at) {
            $this->update(['delivered_at' => now(), 'status' => 'delivered']);
        }
    }

    public function markAsOpened($userAgent = null, $ipAddress = null)
    {
        $this->update([
            'opened_at' => now(),
            'status' => 'opened',
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress
        ]);
        
        // Update campaign stats
        if ($this->campaign) {
            $this->campaign->increment('opened_count');
        }
    }

    public function markAsClicked($userAgent = null, $ipAddress = null)
    {
        $this->update([
            'clicked_at' => now(),
            'status' => 'clicked',
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress
        ]);
        
        // Update campaign stats
        if ($this->campaign) {
            $this->campaign->increment('clicked_count');
        }
    }

    public function markAsBounced($errorMessage = null)
    {
        $this->update([
            'bounced_at' => now(),
            'status' => 'bounced',
            'error_message' => $errorMessage
        ]);
        
        // Update campaign stats
        if ($this->campaign) {
            $this->campaign->increment('bounced_count');
        }
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'sent' => 'green',
            'delivered' => 'blue',
            'opened' => 'purple',
            'clicked' => 'indigo',
            'bounced' => 'orange',
            'failed' => 'red',
            'pending' => 'yellow',
            default => 'gray'
        };
    }
}
