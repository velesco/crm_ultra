<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsentLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contact_id',
        'consent_type',
        'status',
        'given_at',
        'withdrawn_at',
        'source',
        'ip_address',
        'user_agent',
        'legal_basis',
        'purpose',
        'retention_period',
        'processor_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'given_at' => 'datetime',
        'withdrawn_at' => 'datetime',
    ];

    // Consent types
    const TYPE_EMAIL_MARKETING = 'email_marketing';

    const TYPE_SMS_MARKETING = 'sms_marketing';

    const TYPE_WHATSAPP_MARKETING = 'whatsapp_marketing';

    const TYPE_DATA_PROCESSING = 'data_processing';

    const TYPE_PROFILING = 'profiling';

    const TYPE_THIRD_PARTY_SHARING = 'third_party_sharing';

    const TYPE_COOKIES = 'cookies';

    const TYPE_ANALYTICS = 'analytics';

    // Status types
    const STATUS_GIVEN = 'given';

    const STATUS_WITHDRAWN = 'withdrawn';

    const STATUS_EXPIRED = 'expired';

    // Legal basis types
    const BASIS_CONSENT = 'consent';

    const BASIS_CONTRACT = 'contract';

    const BASIS_LEGAL_OBLIGATION = 'legal_obligation';

    const BASIS_VITAL_INTERESTS = 'vital_interests';

    const BASIS_PUBLIC_TASK = 'public_task';

    const BASIS_LEGITIMATE_INTERESTS = 'legitimate_interests';

    // Source types
    const SOURCE_WEBSITE = 'website';

    const SOURCE_API = 'api';

    const SOURCE_IMPORT = 'import';

    const SOURCE_MANUAL = 'manual';

    const SOURCE_FORM = 'form';

    /**
     * Get the contact that this consent belongs to
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the processor (admin user) who handled the consent
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    /**
     * Scope for given consents
     */
    public function scopeGiven($query)
    {
        return $query->where('status', self::STATUS_GIVEN);
    }

    /**
     * Scope for withdrawn consents
     */
    public function scopeWithdrawn($query)
    {
        return $query->where('status', self::STATUS_WITHDRAWN);
    }

    /**
     * Scope for expired consents
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    /**
     * Scope for consents by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('consent_type', $type);
    }

    /**
     * Get all consent types
     */
    public static function getConsentTypes()
    {
        return [
            self::TYPE_EMAIL_MARKETING => 'Email Marketing',
            self::TYPE_SMS_MARKETING => 'SMS Marketing',
            self::TYPE_WHATSAPP_MARKETING => 'WhatsApp Marketing',
            self::TYPE_DATA_PROCESSING => 'Data Processing',
            self::TYPE_PROFILING => 'Profiling',
            self::TYPE_THIRD_PARTY_SHARING => 'Third Party Sharing',
            self::TYPE_COOKIES => 'Cookies',
            self::TYPE_ANALYTICS => 'Analytics',
        ];
    }

    /**
     * Get all statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_GIVEN => 'Given',
            self::STATUS_WITHDRAWN => 'Withdrawn',
            self::STATUS_EXPIRED => 'Expired',
        ];
    }

    /**
     * Get all legal basis types
     */
    public static function getLegalBasisTypes()
    {
        return [
            self::BASIS_CONSENT => 'Consent',
            self::BASIS_CONTRACT => 'Contract',
            self::BASIS_LEGAL_OBLIGATION => 'Legal Obligation',
            self::BASIS_VITAL_INTERESTS => 'Vital Interests',
            self::BASIS_PUBLIC_TASK => 'Public Task',
            self::BASIS_LEGITIMATE_INTERESTS => 'Legitimate Interests',
        ];
    }

    /**
     * Get all source types
     */
    public static function getSourceTypes()
    {
        return [
            self::SOURCE_WEBSITE => 'Website',
            self::SOURCE_API => 'API',
            self::SOURCE_IMPORT => 'Import',
            self::SOURCE_MANUAL => 'Manual',
            self::SOURCE_FORM => 'Form',
        ];
    }

    /**
     * Check if consent is active
     */
    public function isActive()
    {
        return $this->status === self::STATUS_GIVEN &&
               (! $this->retention_period || $this->given_at->addDays($this->retention_period)->isFuture());
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_GIVEN => 'success',
            self::STATUS_WITHDRAWN => 'warning',
            self::STATUS_EXPIRED => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get consent type icon
     */
    public function getConsentTypeIcon()
    {
        return match ($this->consent_type) {
            self::TYPE_EMAIL_MARKETING => 'mail',
            self::TYPE_SMS_MARKETING => 'message-square',
            self::TYPE_WHATSAPP_MARKETING => 'message-circle',
            self::TYPE_DATA_PROCESSING => 'database',
            self::TYPE_PROFILING => 'user',
            self::TYPE_THIRD_PARTY_SHARING => 'share-2',
            self::TYPE_COOKIES => 'cookie',
            self::TYPE_ANALYTICS => 'bar-chart',
            default => 'file-text'
        };
    }

    /**
     * Give consent
     */
    public function giveConsent($source = null, $ipAddress = null, $userAgent = null, $metadata = null)
    {
        $this->status = self::STATUS_GIVEN;
        $this->given_at = now();
        $this->withdrawn_at = null;

        if ($source) {
            $this->source = $source;
        }
        if ($ipAddress) {
            $this->ip_address = $ipAddress;
        }
        if ($userAgent) {
            $this->user_agent = $userAgent;
        }
        if ($metadata) {
            $this->metadata = array_merge($this->metadata ?? [], $metadata);
        }

        $this->save();
    }

    /**
     * Withdraw consent
     */
    public function withdrawConsent($processorId = null, $notes = null)
    {
        $this->status = self::STATUS_WITHDRAWN;
        $this->withdrawn_at = now();

        if ($processorId) {
            $this->processor_id = $processorId;
        }
        if ($notes) {
            $this->notes = $notes;
        }

        $this->save();
    }

    /**
     * Mark as expired
     */
    public function markAsExpired()
    {
        $this->status = self::STATUS_EXPIRED;
        $this->save();
    }
}
