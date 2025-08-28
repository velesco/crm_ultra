<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'prefix',
        'description',
        'permissions',
        'scopes',
        'environment',
        'allowed_ips',
        'rate_limit_per_minute',
        'rate_limit_per_hour',
        'rate_limit_per_day',
        'last_used_at',
        'usage_count',
        'status',
        'expires_at',
        'created_by',
        'updated_by',
        'metadata',
    ];

    protected $casts = [
        'permissions' => 'array',
        'scopes' => 'array',
        'metadata' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'key', // Hide the actual API key in JSON responses
    ];

    protected $appends = [
        'masked_key',
        'is_active',
        'is_expired',
        'days_until_expiry',
    ];

    // Boot method to generate API key automatically
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($apiKey) {
            if (! $apiKey->key) {
                $apiKey->key = $apiKey->generateApiKey();
            }
            if (auth()->check()) {
                $apiKey->created_by = auth()->id();
                $apiKey->updated_by = auth()->id();
            }
        });

        static::updating(function ($apiKey) {
            if (auth()->check()) {
                $apiKey->updated_by = auth()->id();
            }
        });
    }

    // Relationships
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }

    public function scopeByEnvironment($query, $environment)
    {
        return $query->where('environment', $environment);
    }

    public function scopeUsedInLastDays($query, $days = 30)
    {
        return $query->where('last_used_at', '>=', now()->subDays($days));
    }

    public function scopeUnused($query, $days = 30)
    {
        return $query->where(function ($q) use ($days) {
            $q->whereNull('last_used_at')
                ->orWhere('last_used_at', '<', now()->subDays($days));
        });
    }

    // Accessors
    public function getMaskedKeyAttribute()
    {
        if (! $this->key) {
            return null;
        }

        return $this->prefix.'_'.substr($this->key, 0, 8).'...'.substr($this->key, -4);
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active' && ! $this->is_expired;
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (! $this->expires_at) {
            return null;
        }

        return $this->expires_at->diffInDays(now(), false);
    }

    public function getFullKeyAttribute()
    {
        return $this->prefix.'_'.$this->key;
    }

    // Helper Methods
    public function generateApiKey()
    {
        do {
            $key = Str::random(32);
        } while (self::where('key', $key)->exists());

        return $key;
    }

    public function regenerateKey()
    {
        $this->key = $this->generateApiKey();
        $this->save();

        return $this->key;
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function hasPermission($permission)
    {
        if (! $this->permissions) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    public function hasScope($scope)
    {
        if (! $this->scopes) {
            return false;
        }

        return in_array($scope, $this->scopes);
    }

    public function isAllowedIp($ip)
    {
        if (! $this->allowed_ips) {
            return true;
        }
        $allowedIps = explode(',', $this->allowed_ips);

        return in_array(trim($ip), array_map('trim', $allowedIps));
    }

    public function canMakeRequest()
    {
        return $this->is_active && ! $this->is_expired;
    }

    // Rate limiting check methods
    public function getRateLimit($period)
    {
        switch ($period) {
            case 'minute': return $this->rate_limit_per_minute;
            case 'hour': return $this->rate_limit_per_hour;
            case 'day': return $this->rate_limit_per_day;
            default: return 0;
        }
    }

    // Static helper methods
    public static function findByKey($key)
    {
        // Remove prefix if present
        if (strpos($key, '_') !== false) {
            $parts = explode('_', $key, 2);
            $key = $parts[1];
        }

        return self::where('key', $key)->first();
    }

    public static function getAvailablePermissions()
    {
        return [
            'contacts.read' => 'View contacts',
            'contacts.create' => 'Create contacts',
            'contacts.update' => 'Update contacts',
            'contacts.delete' => 'Delete contacts',
            'emails.read' => 'View email campaigns',
            'emails.send' => 'Send emails',
            'sms.read' => 'View SMS messages',
            'sms.send' => 'Send SMS',
            'whatsapp.read' => 'View WhatsApp messages',
            'whatsapp.send' => 'Send WhatsApp messages',
            'segments.read' => 'View segments',
            'segments.create' => 'Create segments',
            'reports.read' => 'View reports',
            'settings.read' => 'View settings',
            'api.admin' => 'Admin API access',
        ];
    }

    public static function getAvailableScopes()
    {
        return [
            'contacts' => 'Contact Management',
            'emails' => 'Email Campaigns',
            'sms' => 'SMS Messaging',
            'whatsapp' => 'WhatsApp Messaging',
            'segments' => 'Contact Segments',
            'reports' => 'Reports & Analytics',
            'settings' => 'System Settings',
            'admin' => 'Administrative Functions',
        ];
    }

    // Statistics methods
    public static function getStatistics()
    {
        return [
            'total' => self::count(),
            'active' => self::active()->count(),
            'expired' => self::expired()->count(),
            'unused_30_days' => self::unused(30)->count(),
            'production' => self::byEnvironment('production')->count(),
            'staging' => self::byEnvironment('staging')->count(),
            'development' => self::byEnvironment('development')->count(),
            'total_usage' => self::sum('usage_count'),
            'last_month_usage' => self::usedInLastDays(30)->sum('usage_count'),
        ];
    }
}
