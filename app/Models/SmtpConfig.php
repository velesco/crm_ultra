<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmtpConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_email',
        'from_name',
        'is_active',
        'daily_limit',
        'hourly_limit',
        'sent_today',
        'sent_this_hour',
        'last_reset_date',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_reset_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Mutators and Accessors
    public function setPasswordAttribute($value)
    {
        if (! empty($value)) {
            $this->attributes['password'] = encrypt($value);
        }
    }

    public function getPasswordAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function emailCampaigns()
    {
        return $this->hasMany(EmailCampaign::class);
    }

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
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
            })
            ->where(function ($q) {
                $q->whereNull('hourly_limit')
                    ->orWhere('sent_this_hour', '<', 'hourly_limit');
            });
    }

    // Methods
    public function canSend()
    {
        if (! $this->is_active) {
            return false;
        }

        // Check daily limit
        if ($this->daily_limit && $this->sent_today >= $this->daily_limit) {
            return false;
        }

        // Check hourly limit
        if ($this->hourly_limit && $this->sent_this_hour >= $this->hourly_limit) {
            return false;
        }

        return true;
    }

    public function incrementSent()
    {
        $now = now();

        // Reset counters if needed
        if ($this->last_reset_date->format('Y-m-d') !== $now->format('Y-m-d')) {
            $this->sent_today = 0;
        }

        if ($this->updated_at->format('Y-m-d H') !== $now->format('Y-m-d H')) {
            $this->sent_this_hour = 0;
        }

        $this->increment('sent_today');
        $this->increment('sent_this_hour');
        $this->update(['last_reset_date' => $now->toDateString()]);
    }

    public function testConnection()
    {
        try {
            // Get decrypted password
            $password = $this->password;

            $transport = \Swift_SmtpTransport::newInstance($this->host, $this->port, $this->encryption)
                ->setUsername($this->username)
                ->setPassword($password);

            $mailer = \Swift_Mailer::newInstance($transport);
            $mailer->getTransport()->start();

            return true;
        } catch (\Exception $e) {
            \Log::error('SMTP Test Connection Error: '.$e->getMessage());

            return false;
        }
    }
}
