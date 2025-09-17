<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Email extends Model
{
    use HasFactory;

    protected $fillable = [
        'google_account_id',
        'thread_id',
        'message_id',
        'gmail_id',
        'subject',
        'body_html',
        'body_text',
        'from_email',
        'from_name',
        'to_recipients',
        'cc_recipients',
        'bcc_recipients',
        'date_sent',
        'date_received',
        'labels',
        'is_read',
        'is_starred',
        'is_important',
        'is_draft',
        'is_sent',
        'is_trash',
        'is_spam',
        'size_bytes',
        'snippet',
        'headers',
        'has_attachments',
        'in_reply_to',
        'references',
    ];

    protected $casts = [
        'to_recipients' => 'array',
        'cc_recipients' => 'array',
        'bcc_recipients' => 'array',
        'labels' => 'array',
        'headers' => 'array',
        'references' => 'array',
        'date_sent' => 'datetime',
        'date_received' => 'datetime',
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'is_important' => 'boolean',
        'is_draft' => 'boolean',
        'is_sent' => 'boolean',
        'is_trash' => 'boolean',
        'is_spam' => 'boolean',
        'has_attachments' => 'boolean',
    ];

    /**
     * Get the Google account that owns the email.
     */
    public function googleAccount(): BelongsTo
    {
        return $this->belongsTo(GoogleAccount::class);
    }

    /**
     * Get all attachments for this email.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(EmailAttachment::class);
    }

    /**
     * Get all emails in the same thread.
     */
    public function threadEmails()
    {
        return $this->where('thread_id', $this->thread_id)
                    ->where('google_account_id', $this->google_account_id)
                    ->orderBy('date_sent', 'asc');
    }

    /**
     * Get the email this is replying to.
     */
    public function parentEmail()
    {
        if (!$this->in_reply_to) {
            return null;
        }

        return $this->where('message_id', $this->in_reply_to)
                    ->where('google_account_id', $this->google_account_id)
                    ->first();
    }

    /**
     * Get all replies to this email.
     */
    public function replies()
    {
        return $this->where('in_reply_to', $this->message_id)
                    ->where('google_account_id', $this->google_account_id)
                    ->orderBy('date_sent', 'asc');
    }

    /**
     * Get all recipients (to, cc, bcc combined).
     */
    public function getAllRecipients(): array
    {
        $recipients = [];
        
        if ($this->to_recipients) {
            $recipients = array_merge($recipients, $this->to_recipients);
        }
        
        if ($this->cc_recipients) {
            $recipients = array_merge($recipients, $this->cc_recipients);
        }
        
        if ($this->bcc_recipients) {
            $recipients = array_merge($recipients, $this->bcc_recipients);
        }
        
        return array_unique($recipients);
    }

    /**
     * Check if email has a specific label.
     */
    public function hasLabel(string $label): bool
    {
        return in_array($label, $this->labels ?? []);
    }

    /**
     * Get formatted size.
     */
    public function getFormattedSize(): string
    {
        if (!$this->size_bytes) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->size_bytes;
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.1f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Scope: Unread emails only.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Starred emails only.
     */
    public function scopeStarred(Builder $query): Builder
    {
        return $query->where('is_starred', true);
    }

    /**
     * Scope: Important emails only.
     */
    public function scopeImportant(Builder $query): Builder
    {
        return $query->where('is_important', true);
    }

    /**
     * Scope: Non-trash, non-spam emails.
     */
    public function scopeInbox(Builder $query): Builder
    {
        return $query->where('is_trash', false)
                    ->where('is_spam', false);
    }

    /**
     * Scope: Emails with specific label.
     */
    public function scopeWithLabel(Builder $query, string $label): Builder
    {
        return $query->whereJsonContains('labels', $label);
    }

    /**
     * Scope: Search in subject and body.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function($q) use ($search) {
            $q->where('subject', 'like', "%{$search}%")
              ->orWhere('body_text', 'like', "%{$search}%")
              ->orWhere('from_name', 'like', "%{$search}%")
              ->orWhere('from_email', 'like', "%{$search}%")
              ->orWhere('snippet', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: Emails from specific sender.
     */
    public function scopeFromSender(Builder $query, string $email): Builder
    {
        return $query->where('from_email', $email);
    }

    /**
     * Scope: Emails in date range.
     */
    public function scopeInDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('date_received', [$startDate, $endDate]);
    }

    /**
     * Mark as read.
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Mark as unread.
     */
    public function markAsUnread(): void
    {
        $this->update(['is_read' => false]);
    }

    /**
     * Toggle starred status.
     */
    public function toggleStar(): void
    {
        $this->update(['is_starred' => !$this->is_starred]);
    }
}
