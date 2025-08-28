<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Communication extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'user_id',
        'type', // email, sms, whatsapp, call, note
        'direction', // inbound, outbound
        'subject',
        'content',
        'status',
        'external_id',
        'metadata',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'replied_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeEmail($query)
    {
        return $query->where('type', 'email');
    }

    public function scopeSms($query)
    {
        return $query->where('type', 'sms');
    }

    public function scopeWhatsapp($query)
    {
        return $query->where('type', 'whatsapp');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Methods
    public function markAsRead()
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function markAsDelivered()
    {
        if (! $this->delivered_at) {
            $this->update(['delivered_at' => now()]);
        }
    }

    public function markAsReplied()
    {
        if (! $this->replied_at) {
            $this->update(['replied_at' => now()]);
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
