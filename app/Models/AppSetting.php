<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'label',
        'description',
        'is_encrypted',
        'is_env_synced',
        'env_key',
        'validation_rules',
        'options',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'options' => 'array',
        'is_encrypted' => 'boolean',
        'is_env_synced' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the value with proper type casting and decryption
     */
    public function getValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            $value = Crypt::decryptString($value);
        }

        return $this->castValue($value);
    }

    /**
     * Set the value with proper encryption
     */
    public function setValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            $value = Crypt::encryptString($value);
        }

        $this->attributes['value'] = $value;
    }

    /**
     * Cast value to proper type
     */
    protected function castValue($value)
    {
        if (is_null($value)) {
            return null;
        }

        return match ($this->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            'array' => is_array($value) ? $value : json_decode($value, true),
            default => (string) $value,
        };
    }

    /**
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->where('is_active', true)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function set($key, $value, $options = [])
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            array_merge([
                'value' => $value,
                'type' => static::detectType($value),
            ], $options)
        );

        // Sync to .env if needed
        if ($setting->is_env_synced && $setting->env_key) {
            static::syncToEnv($setting->env_key, $value);
        }

        return $setting;
    }

    /**
     * Get settings by category
     */
    public static function getByCategory($category)
    {
        return static::where('category', $category)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('label')
                    ->get();
    }

    /**
     * Sync setting to .env file
     */
    public static function syncToEnv($envKey, $value)
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            return false;
        }

        $envContent = File::get($envPath);
        
        // Format value for .env
        $formattedValue = static::formatForEnv($value);
        
        // Check if key exists
        if (Str::contains($envContent, $envKey . '=')) {
            // Update existing key
            $envContent = preg_replace(
                "/^{$envKey}=.*$/m",
                "{$envKey}={$formattedValue}",
                $envContent
            );
        } else {
            // Add new key
            $envContent .= "\n{$envKey}={$formattedValue}";
        }

        File::put($envPath, $envContent);
        
        return true;
    }

    /**
     * Format value for .env file
     */
    protected static function formatForEnv($value)
    {
        if (is_null($value)) {
            return 'null';
        }
        
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        
        if (is_string($value) && (Str::contains($value, ' ') || Str::contains($value, '"'))) {
            return '"' . str_replace('"', '\"', $value) . '"';
        }
        
        return $value;
    }

    /**
     * Detect value type
     */
    protected static function detectType($value)
    {
        if (is_bool($value)) return 'boolean';
        if (is_int($value)) return 'integer';
        if (is_float($value)) return 'float';
        if (is_array($value)) return 'json';
        
        return 'string';
    }

    /**
     * Validate setting value
     */
    public function validateValue($value)
    {
        if (!$this->validation_rules) {
            return true;
        }

        $validator = validator(['value' => $value], ['value' => $this->validation_rules]);
        
        return $validator->passes();
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Generate env_key if not provided
        static::creating(function ($setting) {
            if (!$setting->env_key && $setting->is_env_synced) {
                $setting->env_key = strtoupper(str_replace('.', '_', $setting->key));
            }
        });
    }

    /**
     * Get all categories
     */
    public static function getCategories()
    {
        return collect([
            'general' => 'General',
            'google' => 'Google',
            'sms' => 'SMS',
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            'database' => 'Database',
        ]);
    }

    /**
     * Get all existing categories from database
     */
    public static function getExistingCategories()
    {
        return static::distinct('category')->pluck('category')->sort();
    }

    /**
     * Scope for active settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for env synced settings
     */
    public function scopeEnvSynced($query)
    {
        return $query->where('is_env_synced', true);
    }
}
