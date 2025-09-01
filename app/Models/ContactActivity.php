<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'type',
        'description',
        'metadata',
        'performed_by',
        'performed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'performed_at' => 'datetime',
    ];

    // Activity types
    const TYPE_EMAIL_SENT = 'email_sent';
    const TYPE_EMAIL_OPENED = 'email_opened';
    const TYPE_EMAIL_CLICKED = 'email_clicked';
    const TYPE_SMS_SENT = 'sms_sent';
    const TYPE_SMS_DELIVERED = 'sms_delivered';
    const TYPE_WHATSAPP_SENT = 'whatsapp_sent';
    const TYPE_WHATSAPP_DELIVERED = 'whatsapp_delivered';
    const TYPE_CONTACT_CREATED = 'contact_created';
    const TYPE_CONTACT_UPDATED = 'contact_updated';
    const TYPE_SEGMENT_ADDED = 'segment_added';
    const TYPE_SEGMENT_REMOVED = 'segment_removed';
    const TYPE_NOTE_ADDED = 'note_added';

    /**
     * Get the contact that this activity belongs to
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the user who performed this activity
     */
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope for activities by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get all activity types
     */
    public static function getActivityTypes()
    {
        return [
            self::TYPE_EMAIL_SENT => 'Email Sent',
            self::TYPE_EMAIL_OPENED => 'Email Opened',
            self::TYPE_EMAIL_CLICKED => 'Email Clicked',
            self::TYPE_SMS_SENT => 'SMS Sent',
            self::TYPE_SMS_DELIVERED => 'SMS Delivered',
            self::TYPE_WHATSAPP_SENT => 'WhatsApp Sent',
            self::TYPE_WHATSAPP_DELIVERED => 'WhatsApp Delivered',
            self::TYPE_CONTACT_CREATED => 'Contact Created',
            self::TYPE_CONTACT_UPDATED => 'Contact Updated',
            self::TYPE_SEGMENT_ADDED => 'Added to Segment',
            self::TYPE_SEGMENT_REMOVED => 'Removed from Segment',
            self::TYPE_NOTE_ADDED => 'Note Added',
        ];
    }

    /**
     * Get activity type icon
     */
    public function getActivityTypeIcon()
    {
        return match ($this->type) {
            self::TYPE_EMAIL_SENT, self::TYPE_EMAIL_OPENED, self::TYPE_EMAIL_CLICKED => 'mail',
            self::TYPE_SMS_SENT, self::TYPE_SMS_DELIVERED => 'message-square',
            self::TYPE_WHATSAPP_SENT, self::TYPE_WHATSAPP_DELIVERED => 'message-circle',
            self::TYPE_CONTACT_CREATED => 'user-plus',
            self::TYPE_CONTACT_UPDATED => 'edit',
            self::TYPE_SEGMENT_ADDED, self::TYPE_SEGMENT_REMOVED => 'tag',
            self::TYPE_NOTE_ADDED => 'file-text',
            default => 'activity'
        };
    }

    /**
     * Get activity type color
     */
    public function getActivityTypeColor()
    {
        return match ($this->type) {
            self::TYPE_EMAIL_SENT, self::TYPE_EMAIL_OPENED, self::TYPE_EMAIL_CLICKED => 'blue',
            self::TYPE_SMS_SENT, self::TYPE_SMS_DELIVERED => 'green',
            self::TYPE_WHATSAPP_SENT, self::TYPE_WHATSAPP_DELIVERED => 'green',
            self::TYPE_CONTACT_CREATED => 'green',
            self::TYPE_CONTACT_UPDATED => 'yellow',
            self::TYPE_SEGMENT_ADDED => 'blue',
            self::TYPE_SEGMENT_REMOVED => 'red',
            self::TYPE_NOTE_ADDED => 'gray',
            default => 'gray'
        };
    }
}
