<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'provider_id',
        'to_number',
        'message',
        'status',
        'external_id',
        'cost',
        'delivered_at',
        'error_message',
        'metadata'
    ];

    protected $casts = [
        'cost' => 'decimal:4',
        'delivered_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function provider()
    {
        return $this->belongsTo(SmsProvider::class, 'provider_id');
    }

    // Scopes
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Methods
    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);
    }

    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage
        ]);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'sent' => 'green',
            'delivered' => 'blue',
            'failed' => 'red',
            'pending' => 'yellow',
            default => 'gray'
        };
    }
}
