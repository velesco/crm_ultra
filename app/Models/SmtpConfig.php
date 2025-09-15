<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmtpConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'provider',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_email',
        'from_name',
        'is_active',
        'priority',
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
            
            // DEBUG: Log the decrypted password for testing
            \Log::info('SMTP Test - Password Debug', [
                'host' => $this->host,
                'username' => $this->username,
                'encrypted_password_length' => strlen($this->attributes['password'] ?? ''),
                'decrypted_password' => $password, // CAREFUL: This will log the actual password
                'decrypted_password_length' => strlen($password ?? ''),
                'password_starts_with' => substr($password ?? '', 0, 3) . '***'
            ]);
            
            // Validate basic settings
            if (empty($this->host) || empty($this->username) || empty($password)) {
                \Log::error('SMTP Test: Missing required settings', [
                    'host' => $this->host,
                    'username' => $this->username,
                    'password_set' => !empty($password),
                    'password_value' => $password // DEBUG: Show actual password value
                ]);
                return false;
            }

            // Use Laravel's built-in SMTP testing approach
            $config = [
                'transport' => 'smtp',
                'host' => $this->host,
                'port' => $this->port,
                'encryption' => $this->encryption === 'tls' ? 'tls' : ($this->encryption === 'ssl' ? 'ssl' : null),
                'username' => $this->username,
                'password' => $password,
                'timeout' => 10,
            ];
            
            // Create a temporary mailer instance for testing
            $transport = \Illuminate\Mail\Transport\SesTransport::class;
            
            // Use Symfony's SMTP transport directly for testing
            if ($this->encryption === 'tls' && $this->port == 587) {
                // For TLS/STARTTLS connections (like Hostinger)
                $dsn = "smtp://{$this->username}:" . urlencode($password) . "@{$this->host}:{$this->port}?encryption=tls";
            } elseif ($this->encryption === 'ssl' && $this->port == 465) {
                // For SSL connections
                $dsn = "smtp://{$this->username}:" . urlencode($password) . "@{$this->host}:{$this->port}?encryption=ssl";
            } else {
                // For plain connections
                $dsn = "smtp://{$this->username}:" . urlencode($password) . "@{$this->host}:{$this->port}";
            }
            
            \Log::info('SMTP Test - DSN Debug', [
                'dsn' => $dsn,
                'host' => $this->host,
                'port' => $this->port,
                'encryption' => $this->encryption
            ]);
            
            // Test using Symfony Mailer Transport
            $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
            
            // Try to connect and authenticate
            $transport->start();
            
            \Log::info('SMTP Test Connection Success', [
                'host' => $this->host,
                'port' => $this->port,
                'encryption' => $this->encryption,
                'username' => $this->username,
                'password_used' => $password // DEBUG: Show successful password
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('SMTP Test Connection Error: ' . $e->getMessage(), [
                'host' => $this->host,
                'port' => $this->port,
                'encryption' => $this->encryption,
                'username' => $this->username,
                'password_attempted' => $password ?? 'NULL', // DEBUG: Show attempted password
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }
    
    /**
     * Load usage statistics for this SMTP config
     */
    public function loadUsageStats()
    {
        // Load email campaign statistics with correct column names
        $this->campaignStats = $this->emailCampaigns()
            ->selectRaw('COUNT(*) as total_campaigns')
            ->selectRaw('SUM(sent_count) as total_emails_sent')
            ->selectRaw('SUM(delivered_count) as total_delivered')
            ->selectRaw('SUM(opened_count) as total_opened')
            ->selectRaw('SUM(clicked_count) as total_clicked')
            ->first();
            
        // Load recent email logs (last 30 days)
        $thirtyDaysAgo = now()->subDays(30);
        $this->recentStats = $this->emailLogs()
            ->where('sent_at', '>=', $thirtyDaysAgo)
            ->selectRaw('COUNT(*) as recent_sent')
            ->selectRaw('COUNT(CASE WHEN status = "delivered" THEN 1 END) as recent_delivered')
            ->selectRaw('COUNT(CASE WHEN opened_at IS NOT NULL THEN 1 END) as recent_opened')
            ->selectRaw('COUNT(CASE WHEN clicked_at IS NOT NULL THEN 1 END) as recent_clicked')
            ->first();
            
        // Load daily usage for last 7 days
        $this->dailyUsage = $this->emailLogs()
            ->where('sent_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(sent_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Calculate usage rates with safe null handling
        $totalSent = $this->campaignStats->total_emails_sent ?? 0;
        if ($totalSent > 0) {
            $this->deliveryRate = round((($this->campaignStats->total_delivered ?? 0) / $totalSent) * 100, 2);
            $this->openRate = round((($this->campaignStats->total_opened ?? 0) / $totalSent) * 100, 2);
            $this->clickRate = round((($this->campaignStats->total_clicked ?? 0) / $totalSent) * 100, 2);
        } else {
            $this->deliveryRate = 0;
            $this->openRate = 0;
            $this->clickRate = 0;
        }
        
        return $this;
    }
}
