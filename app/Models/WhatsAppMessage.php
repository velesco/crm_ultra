<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'session_id',
        'contact_id',
        'user_id',
        'phone_number',
        'message',
        'message_type',
        'media_url',
        'direction',
        'status',
        'whatsapp_message_id',
        'sent_at',
        'delivered_at',
        'read_at',
        'scheduled_at',
        'error_message',
        // Original fields
        'message_id',
        'from_number',
        'to_number', 
        'content',
        'media_type',
        'metadata',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(WhatsAppSession::class, 'session_id');
    }

    public function whatsAppSession()
    {
        return $this->belongsTo(WhatsAppSession::class, 'session_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('message_type', $type);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Methods
    public function markAsDelivered()
    {
        if (! $this->delivered_at) {
            $this->update(['delivered_at' => now(), 'status' => 'delivered']);
        }
    }

    public function markAsRead()
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now(), 'status' => 'read']);
        }
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'sent' => 'green',
            'delivered' => 'blue',
            'read' => 'purple',
            'failed' => 'red',
            'pending' => 'yellow',
            default => 'gray'
        };
    }
}
