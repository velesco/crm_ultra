<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'whatsapp',
        'company',
        'position',
        'address',
        'city',
        'country',
        'website',
        'tags',
        'notes',
        'custom_fields',
        'status',
        'source',
        'source_metadata',
        'first_email_at',
        'last_email_at',
        'email_count',
        'linkedin_url',
        'twitter_handle',
        'social_profiles',
        'team_id',
        'created_by',
        'assigned_to',
    ];

    protected $casts = [
        'tags' => 'array',
        'custom_fields' => 'array',
        'source_metadata' => 'array',
        'social_profiles' => 'array',
        'first_email_at' => 'datetime',
        'last_email_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get all emails where this contact is a participant (from or to).
     */
    public function relatedEmails()
    {
        return Email::where(function($query) {
            $query->where('from_email', $this->email)
                  ->orWhereJsonContains('to_recipients', $this->email)
                  ->orWhereJsonContains('cc_recipients', $this->email)
                  ->orWhereJsonContains('bcc_recipients', $this->email);
        })->orderBy('date_received', 'desc');
    }

    /**
     * Update email statistics for this contact.
     */
    public function updateEmailStats()
    {
        $emails = $this->relatedEmails()->get();
        
        if ($emails->isNotEmpty()) {
            $this->update([
                'first_email_at' => $emails->min('date_received'),
                'last_email_at' => $emails->max('date_received'),
                'email_count' => $emails->count(),
            ]);
        }
    }

    /**
     * Check if contact was created from Gmail.
     */
    public function isFromGmail(): bool
    {
        return $this->source === 'gmail';
    }

    /**
     * Check if contact was created from Google Sheets.
     */
    public function isFromSheets(): bool
    {
        return $this->source === 'sheets';
    }

    /**
     * Get full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Scope: Contacts from Gmail.
     */
    public function scopeFromGmail($query)
    {
        return $query->where('source', 'gmail');
    }

    /**
     * Scope: Contacts from Google Sheets.
     */
    public function scopeFromSheets($query)
    {
        return $query->where('source', 'sheets');
    }

    /**
     * Scope: Contacts for specific team.
     */
    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope: Contacts with recent email activity.
     */
    public function scopeWithRecentEmails($query, $days = 30)
    {
        return $query->where('last_email_at', '>=', now()->subDays($days));
    }

    public function emailCampaigns()
    {
        return $this->belongsToMany(EmailCampaign::class, 'email_campaign_contacts');
    }

    public function communications()
    {
        return $this->hasMany(Communication::class);
    }

    public function segments()
    {
        return $this->belongsToMany(ContactSegment::class, 'contact_segment_members');
    }

    public function consentLogs()
    {
        return $this->hasMany(ConsentLog::class);
    }

    public function dataRequests()
    {
        return $this->hasMany(DataRequest::class);
    }

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
    }

    public function smsMessages()
    {
        return $this->hasMany(SmsMessage::class);
    }

    public function whatsappMessages()
    {
        return $this->hasMany(WhatsAppMessage::class);
    }

    public function contactActivities()
    {
        return $this->hasMany(ContactActivity::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    // Accessors
    public function getLastActivityAtAttribute()
    {
        return $this->communications()->latest()->first()?->created_at;
    }

    // Methods
    public function addTag($tag)
    {
        $tags = $this->tags ?? [];
        if (! in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->tags = $tags;
            $this->save();
        }
    }

    public function removeTag($tag)
    {
        $tags = $this->tags ?? [];
        $this->tags = array_values(array_filter($tags, fn ($t) => $t !== $tag));
        $this->save();
    }
}
