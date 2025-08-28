<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contact_id',
        'request_type',
        'type', // Alias for request_type
        'status',
        'email',
        'phone',
        'full_name',
        'request_details',
        'verification_token',
        'verified_at',
        'processed_at',
        'completed_at',
        'rejection_reason',
        'processor_id',
        'file_path',
        'export_file_path', // Alias for file_path
        'expires_at',
        'requested_at', // Alias for created_at
        'notes', // Alias for rejection_reason
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'verified_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Request types
    const TYPE_EXPORT = 'export';

    const TYPE_DELETE = 'delete';

    const TYPE_RECTIFICATION = 'rectification';

    const TYPE_PORTABILITY = 'portability';

    const TYPE_RESTRICTION = 'restriction';

    const TYPE_OBJECTION = 'objection';

    // Status types
    const STATUS_PENDING = 'pending';

    const STATUS_VERIFIED = 'verified';

    const STATUS_PROCESSING = 'processing';

    const STATUS_COMPLETED = 'completed';

    const STATUS_REJECTED = 'rejected';

    const STATUS_EXPIRED = 'expired';

    /**
     * Get the contact that made the request
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the processor (admin user) who handled the request
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for verified requests
     */
    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    /**
     * Scope for processing requests
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for completed requests
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope for overdue requests (over 30 days old and not completed)
     */
    public function scopeOverdue($query)
    {
        return $query->where('created_at', '<', now()->subDays(30))
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_VERIFIED, self::STATUS_PROCESSING]);
    }

    /**
     * Scope for expired requests
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope for requests by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('request_type', $type);
    }

    /**
     * Get all request types
     */
    public static function getRequestTypes()
    {
        return [
            self::TYPE_EXPORT => 'Data Export',
            self::TYPE_DELETE => 'Data Deletion',
            self::TYPE_RECTIFICATION => 'Data Rectification',
            self::TYPE_PORTABILITY => 'Data Portability',
            self::TYPE_RESTRICTION => 'Processing Restriction',
            self::TYPE_OBJECTION => 'Processing Objection',
        ];
    }

    /**
     * Get all statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending Verification',
            self::STATUS_VERIFIED => 'Verified',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
        ];
    }

    /**
     * Check if request is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if request can be processed
     */
    public function canBeProcessed()
    {
        return in_array($this->status, [self::STATUS_VERIFIED, self::STATUS_PROCESSING]) && ! $this->isExpired();
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_VERIFIED => 'info',
            self::STATUS_PROCESSING => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_EXPIRED => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Get request type icon
     */
    public function getRequestTypeIcon()
    {
        return match ($this->request_type) {
            self::TYPE_EXPORT => 'download',
            self::TYPE_DELETE => 'trash',
            self::TYPE_RECTIFICATION => 'edit',
            self::TYPE_PORTABILITY => 'share',
            self::TYPE_RESTRICTION => 'lock',
            self::TYPE_OBJECTION => 'x-circle',
            default => 'file-text'
        };
    }

    /**
     * Generate verification token
     */
    public function generateVerificationToken()
    {
        $this->verification_token = bin2hex(random_bytes(32));
        $this->expires_at = now()->addDays(30);
        $this->save();

        return $this->verification_token;
    }

    /**
     * Verify the request
     */
    public function verify()
    {
        $this->status = self::STATUS_VERIFIED;
        $this->verified_at = now();
        $this->save();
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing($processorId = null)
    {
        $this->status = self::STATUS_PROCESSING;
        $this->processed_at = now();
        if ($processorId) {
            $this->processor_id = $processorId;
        }
        $this->save();
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted($filePath = null)
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        if ($filePath) {
            $this->file_path = $filePath;
        }
        $this->save();
    }

    /**
     * Reject the request
     */
    public function reject($reason = null, $processorId = null)
    {
        $this->status = self::STATUS_REJECTED;
        $this->rejection_reason = $reason;
        if ($processorId) {
            $this->processor_id = $processorId;
        }
        $this->save();
    }

    /**
     * Accessor for type (alias for request_type)
     */
    public function getTypeAttribute()
    {
        return $this->request_type;
    }

    /**
     * Mutator for type (alias for request_type)
     */
    public function setTypeAttribute($value)
    {
        $this->attributes['request_type'] = $value;
    }

    /**
     * Accessor for export_file_path (alias for file_path)
     */
    public function getExportFilePathAttribute()
    {
        return $this->file_path;
    }

    /**
     * Mutator for export_file_path (alias for file_path)
     */
    public function setExportFilePathAttribute($value)
    {
        $this->attributes['file_path'] = $value;
    }

    /**
     * Accessor for requested_at (alias for created_at)
     */
    public function getRequestedAtAttribute()
    {
        return $this->created_at;
    }

    /**
     * Accessor for notes (alias for rejection_reason)
     */
    public function getNotesAttribute()
    {
        return $this->rejection_reason;
    }

    /**
     * Mutator for notes (alias for rejection_reason)
     */
    public function setNotesAttribute($value)
    {
        $this->attributes['rejection_reason'] = $value;
    }
}
