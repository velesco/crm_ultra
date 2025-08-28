<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'validation_rules',
        'options',
        'is_encrypted',
        'is_public',
        'requires_restart',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'options' => 'array',
        'is_encrypted' => 'boolean',
        'is_public' => 'boolean',
        'requires_restart' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function ($setting) {
            // Auto-set updated_by
            if (auth()->check()) {
                $setting->updated_by = auth()->id();
                
                if (!$setting->exists) {
                    $setting->created_by = auth()->id();
                }
            }
        });

        static::saved(function ($setting) {
            // Clear cache after saving
            self::clearSettingsCache();
        });

        static::deleted(function ($setting) {
            // Clear cache after deleting
            self::clearSettingsCache();
        });
    }

    /**
     * Relationships
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Accessors & Mutators
     */
    public function getValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            try {
                return Crypt::decrypt($value);
            } catch (\Exception $e) {
                return $value; // Return as-is if decryption fails
            }
        }

        // Cast value based on type
        return match ($this->type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value
        };
    }

    public function setValueAttribute($value)
    {
        if ($this->is_encrypted && $value !== null) {
            $this->attributes['value'] = Crypt::encrypt($value);
        } else {
            // Store value based on type
            $this->attributes['value'] = match ($this->type) {
                'boolean' => $value ? '1' : '0',
                'json' => json_encode($value),
                default => $value
            };
        }
    }

    /**
     * Scopes
     */
    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    /**
     * Static methods for easy access
     */
    public static function get($key, $default = null)
    {
        $settings = self::getAllCached();
        return $settings[$key] ?? $default;
    }

    public static function set($key, $value, $type = 'string')
    {
        $setting = self::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->type = $type;
        
        if (!$setting->exists) {
            $setting->group = 'general';
            $setting->label = ucfirst(str_replace(['_', '-'], ' ', $key));
        }
        
        $setting->save();
        return $setting;
    }

    public static function getAllCached()
    {
        return Cache::remember('system_settings', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    public static function getByGroupCached($group)
    {
        return Cache::remember("system_settings_group_{$group}", 3600, function () use ($group) {
            return self::byGroup($group)->ordered()->get()->keyBy('key');
        });
    }

    public static function clearSettingsCache()
    {
        Cache::forget('system_settings');
        
        // Clear group caches
        $groups = self::distinct('group')->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("system_settings_group_{$group}");
        }
    }

    /**
     * Helper methods
     */
    public function getFormattedValue()
    {
        $value = $this->value;

        return match ($this->type) {
            'boolean' => $value ? 'Yes' : 'No',
            'json' => json_encode($value, JSON_PRETTY_PRINT),
            'encrypted' => '••••••••',
            default => $value
        };
    }

    public function isEditable()
    {
        // Add any business logic for non-editable settings
        $readOnlyKeys = ['app_version', 'installation_id', 'license_key'];
        return !in_array($this->key, $readOnlyKeys);
    }
}
