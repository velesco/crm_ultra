<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'phone',
        'avatar',
        'timezone',
        'language',
        'is_active',
        'last_login_at',
        'settings',
        'department',
        'position',
        'notes',
        'login_count',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'settings' => 'array',
        'login_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // User Management Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    // CRM Relationships
    public function createdContacts()
    {
        return $this->hasMany(Contact::class, 'created_by');
    }

    // Alias for consistency with controller
    public function contactsCreated()
    {
        return $this->createdContacts();
    }

    public function assignedContacts()
    {
        return $this->hasMany(Contact::class, 'assigned_to');
    }

    public function emailCampaigns()
    {
        return $this->hasMany(EmailCampaign::class, 'created_by');
    }

    public function smtpConfigs()
    {
        return $this->hasMany(SmtpConfig::class, 'created_by');
    }

    public function smsProviders()
    {
        return $this->hasMany(SmsProvider::class, 'created_by');
    }

    public function whatsappSessions()
    {
        return $this->hasMany(WhatsAppSession::class, 'created_by');
    }

    public function communications()
    {
        return $this->hasMany(Communication::class);
    }

    public function contactSegments()
    {
        return $this->hasMany(ContactSegment::class, 'created_by');
    }

    public function emailTemplates()
    {
        return $this->hasMany(EmailTemplate::class, 'created_by');
    }

    public function dataImports()
    {
        return $this->hasMany(DataImport::class, 'created_by');
    }

    public function googleSheetsIntegrations()
    {
        return $this->hasMany(GoogleSheetsIntegration::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Methods
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1));
        }

        return strtoupper(substr($this->name, 0, 2));
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            // Check if it's a Google avatar URL
            if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
                return $this->avatar;
            }

            // Otherwise, it's a local file
            return asset('storage/avatars/'.$this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }

    public function hasActiveSmtpConfig()
    {
        return $this->smtpConfigs()->active()->exists();
    }

    public function hasActiveSmsProvider()
    {
        return $this->smsProviders()->active()->exists();
    }

    public function hasActiveWhatsAppSession()
    {
        return $this->whatsappSessions()->active()->connected()->exists();
    }

    // Permission helpers
    public function canManageContacts()
    {
        return $this->hasPermissionTo('manage contacts') || $this->hasRole('admin');
    }

    public function canSendEmails()
    {
        return $this->hasPermissionTo('send emails') || $this->hasRole(['admin', 'manager']);
    }

    public function canSendSms()
    {
        return $this->hasPermissionTo('send sms') || $this->hasRole(['admin', 'manager']);
    }

    public function canUseWhatsApp()
    {
        return $this->hasPermissionTo('use whatsapp') || $this->hasRole(['admin', 'manager']);
    }

    public function canImportData()
    {
        return $this->hasPermissionTo('import data') || $this->hasRole(['admin', 'manager']);
    }

    public function canManageSettings()
    {
        return $this->hasPermissionTo('manage settings') || $this->hasRole('admin');
    }
}
