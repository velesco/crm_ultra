<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailCampaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'subject',
        'content',
        'template_id',
        'smtp_config_id',
        'status',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'opened_count',
        'clicked_count',
        'bounced_count',
        'failed_count',
        'created_by',
        'settings',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function smtpConfig()
    {
        return $this->belongsTo(SmtpConfig::class);
    }

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'email_campaign_contacts')
            ->withPivot(['status', 'sent_at', 'delivered_at', 'opened_at', 'clicked_at', 'bounced_at', 'error_message'])
            ->withTimestamps();
    }

    public function logs()
    {
        return $this->hasMany(EmailLog::class, 'campaign_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    // Methods
    public function getOpenRateAttribute()
    {
        return $this->sent_count > 0 ? round(($this->opened_count / $this->sent_count) * 100, 2) : 0;
    }

    public function getClickRateAttribute()
    {
        return $this->sent_count > 0 ? round(($this->clicked_count / $this->sent_count) * 100, 2) : 0;
    }

    public function getBounceRateAttribute()
    {
        return $this->sent_count > 0 ? round(($this->bounced_count / $this->sent_count) * 100, 2) : 0;
    }
}
