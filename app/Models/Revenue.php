<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'reference_id',
        'amount',
        'currency',
        'type',
        'status',
        'source_type',
        'source_id',
        'channel',
        'contact_id',
        'customer_name',
        'customer_email',
        'cost',
        'tax_amount',
        'commission',
        'revenue_date',
        'confirmed_at',
        'refunded_at',
        'metadata',
        'notes',
        'created_by',
        'created_source',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'revenue_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $dates = [
        'revenue_date',
        'confirmed_at',
        'refunded_at',
    ];

    // Revenue types
    const TYPE_SUBSCRIPTION = 'subscription';

    const TYPE_ONE_TIME = 'one_time';

    const TYPE_COMMISSION = 'commission';

    const TYPE_REFUND = 'refund';

    const TYPE_BONUS = 'bonus';

    // Revenue statuses
    const STATUS_PENDING = 'pending';

    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_REFUNDED = 'refunded';

    const STATUS_CANCELLED = 'cancelled';

    // Revenue channels
    const CHANNEL_EMAIL = 'email';

    const CHANNEL_SMS = 'sms';

    const CHANNEL_WHATSAPP = 'whatsapp';

    const CHANNEL_DIRECT = 'direct';

    const CHANNEL_API = 'api';

    const CHANNEL_MANUAL = 'manual';

    // Source types
    const SOURCE_EMAIL_CAMPAIGN = 'email_campaign';

    const SOURCE_SMS_CAMPAIGN = 'sms_campaign';

    const SOURCE_WHATSAPP_MESSAGE = 'whatsapp_message';

    const SOURCE_MANUAL = 'manual';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($revenue) {
            if (empty($revenue->transaction_id)) {
                $revenue->transaction_id = 'TXN-'.strtoupper(uniqid()).'-'.time();
            }

            if (empty($revenue->revenue_date)) {
                $revenue->revenue_date = now();
            }
        });
    }

    /**
     * Relationships
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function emailCampaign()
    {
        return $this->belongsTo(EmailCampaign::class, 'source_id')
            ->where('source_type', self::SOURCE_EMAIL_CAMPAIGN);
    }

    public function smsMessage()
    {
        return $this->belongsTo(SmsMessage::class, 'source_id')
            ->where('source_type', self::SOURCE_SMS_CAMPAIGN);
    }

    public function whatsappMessage()
    {
        return $this->belongsTo(WhatsAppMessage::class, 'source_id')
            ->where('source_type', self::SOURCE_WHATSAPP_MESSAGE);
    }

    /**
     * Get the source model dynamically
     */
    public function source()
    {
        switch ($this->source_type) {
            case self::SOURCE_EMAIL_CAMPAIGN:
                return $this->belongsTo(EmailCampaign::class, 'source_id');
            case self::SOURCE_SMS_CAMPAIGN:
                return $this->belongsTo(SmsMessage::class, 'source_id');
            case self::SOURCE_WHATSAPP_MESSAGE:
                return $this->belongsTo(WhatsAppMessage::class, 'source_id');
            default:
                return null;
        }
    }

    /**
     * Scopes
     */
    public function scopeConfirmed(Builder $query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopePending(Builder $query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRefunded(Builder $query)
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }

    public function scopeThisMonth(Builder $query)
    {
        return $query->whereBetween('revenue_date', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
    }

    public function scopeThisYear(Builder $query)
    {
        return $query->whereBetween('revenue_date', [
            now()->startOfYear(),
            now()->endOfYear(),
        ]);
    }

    public function scopeDateRange(Builder $query, $startDate, $endDate)
    {
        return $query->whereBetween('revenue_date', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);
    }

    public function scopeByChannel(Builder $query, $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeByType(Builder $query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByContact(Builder $query, $contactId)
    {
        return $query->where('contact_id', $contactId);
    }

    /**
     * Accessors
     */
    public function getNetRevenueAttribute()
    {
        return $this->amount - $this->cost;
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2).' '.$this->currency;
    }

    public function getFormattedNetRevenueAttribute()
    {
        return number_format($this->net_revenue, 2).' '.$this->currency;
    }

    public function getStatusBadgeAttribute()
    {
        $classes = [
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_CONFIRMED => 'bg-success',
            self::STATUS_REFUNDED => 'bg-danger',
            self::STATUS_CANCELLED => 'bg-secondary',
        ];

        return $classes[$this->status] ?? 'bg-secondary';
    }

    public function getChannelIconAttribute()
    {
        $icons = [
            self::CHANNEL_EMAIL => 'fas fa-envelope',
            self::CHANNEL_SMS => 'fas fa-sms',
            self::CHANNEL_WHATSAPP => 'fab fa-whatsapp',
            self::CHANNEL_DIRECT => 'fas fa-handshake',
            self::CHANNEL_API => 'fas fa-plug',
            self::CHANNEL_MANUAL => 'fas fa-user',
        ];

        return $icons[$this->channel] ?? 'fas fa-question';
    }

    /**
     * Helper methods
     */
    public function confirm()
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    public function refund($reason = null)
    {
        $this->update([
            'status' => self::STATUS_REFUNDED,
            'refunded_at' => now(),
            'notes' => $this->notes ? $this->notes."\n\nRefund reason: ".$reason : 'Refund reason: '.$reason,
        ]);
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $this->notes ? $this->notes."\n\nCancellation reason: ".$reason : 'Cancellation reason: '.$reason,
        ]);
    }

    public function isConfirmed()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRefunded()
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Static helper methods
     */
    public static function getTotalRevenue($startDate = null, $endDate = null)
    {
        $query = static::confirmed();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->sum('amount');
    }

    public static function getNetRevenue($startDate = null, $endDate = null)
    {
        $query = static::confirmed();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->selectRaw('SUM(amount - cost) as net_revenue')->first()->net_revenue ?? 0;
    }

    public static function getRevenueByChannel($startDate = null, $endDate = null)
    {
        $query = static::confirmed();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->selectRaw('channel, SUM(amount) as total_revenue, COUNT(*) as transaction_count')
            ->groupBy('channel')
            ->get();
    }

    public static function getRevenueByType($startDate = null, $endDate = null)
    {
        $query = static::confirmed();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->selectRaw('type, SUM(amount) as total_revenue, COUNT(*) as transaction_count')
            ->groupBy('type')
            ->get();
    }

    public static function getMonthlyRevenue($year = null)
    {
        $year = $year ?? now()->year;

        return static::confirmed()
            ->whereYear('revenue_date', $year)
            ->selectRaw('MONTH(revenue_date) as month, SUM(amount) as total_revenue, COUNT(*) as transaction_count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public static function getTopCustomersByRevenue($limit = 10, $startDate = null, $endDate = null)
    {
        $query = static::confirmed()->whereNotNull('contact_id');

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->selectRaw('contact_id, SUM(amount) as total_revenue, COUNT(*) as transaction_count')
            ->with('contact:id,first_name,last_name,email,company')
            ->groupBy('contact_id')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getGrowthRate($startDate, $endDate)
    {
        $currentRevenue = static::getTotalRevenue($startDate, $endDate);

        $previousStart = Carbon::parse($startDate)->subDays(
            Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate))
        );
        $previousEnd = Carbon::parse($startDate)->subDay();

        $previousRevenue = static::getTotalRevenue($previousStart, $previousEnd);

        if ($previousRevenue > 0) {
            return (($currentRevenue - $previousRevenue) / $previousRevenue) * 100;
        }

        return 0;
    }

    /**
     * Create revenue from campaign activity
     */
    public static function createFromEmailOpen($emailLog, $value = 0.10)
    {
        return static::create([
            'amount' => $value,
            'type' => self::TYPE_COMMISSION,
            'status' => self::STATUS_CONFIRMED,
            'source_type' => self::SOURCE_EMAIL_CAMPAIGN,
            'source_id' => $emailLog->email_campaign_id,
            'channel' => self::CHANNEL_EMAIL,
            'contact_id' => $emailLog->contact_id,
            'customer_email' => $emailLog->contact->email ?? null,
            'customer_name' => $emailLog->contact ? $emailLog->contact->first_name.' '.$emailLog->contact->last_name : null,
            'cost' => 0.01, // Estimated email cost
            'revenue_date' => $emailLog->opened_at ?? now(),
            'created_source' => 'system',
            'metadata' => [
                'email_log_id' => $emailLog->id,
                'campaign_name' => $emailLog->emailCampaign->subject ?? null,
            ],
        ]);
    }

    public static function createFromSmsDelivery($smsMessage, $value = 0.05)
    {
        $contact = Contact::where('phone', $smsMessage->to_number)->first();

        return static::create([
            'amount' => $value,
            'type' => self::TYPE_COMMISSION,
            'status' => self::STATUS_CONFIRMED,
            'source_type' => self::SOURCE_SMS_CAMPAIGN,
            'source_id' => $smsMessage->id,
            'channel' => self::CHANNEL_SMS,
            'contact_id' => $contact?->id,
            'customer_name' => $contact ? $contact->first_name.' '.$contact->last_name : null,
            'cost' => $smsMessage->cost ?? 0.02, // SMS cost
            'revenue_date' => $smsMessage->sent_at ?? now(),
            'created_source' => 'system',
            'metadata' => [
                'sms_message_id' => $smsMessage->id,
                'provider' => $smsMessage->provider ?? 'unknown',
            ],
        ]);
    }

    public static function createFromWhatsAppMessage($whatsappMessage, $value = 0.02)
    {
        $contact = Contact::where('phone', $whatsappMessage->to_number)->first();

        return static::create([
            'amount' => $value,
            'type' => self::TYPE_COMMISSION,
            'status' => self::STATUS_CONFIRMED,
            'source_type' => self::SOURCE_WHATSAPP_MESSAGE,
            'source_id' => $whatsappMessage->id,
            'channel' => self::CHANNEL_WHATSAPP,
            'contact_id' => $contact?->id,
            'customer_name' => $contact ? $contact->first_name.' '.$contact->last_name : null,
            'cost' => 0.005, // WhatsApp cost
            'revenue_date' => $whatsappMessage->sent_at ?? now(),
            'created_source' => 'system',
            'metadata' => [
                'whatsapp_message_id' => $whatsappMessage->id,
                'session_id' => $whatsappMessage->session_id ?? null,
            ],
        ]);
    }

    /**
     * Get revenue constants for forms
     */
    public static function getTypes()
    {
        return [
            self::TYPE_SUBSCRIPTION => 'Subscription',
            self::TYPE_ONE_TIME => 'One Time',
            self::TYPE_COMMISSION => 'Commission',
            self::TYPE_REFUND => 'Refund',
            self::TYPE_BONUS => 'Bonus',
        ];
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_REFUNDED => 'Refunded',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function getChannels()
    {
        return [
            self::CHANNEL_EMAIL => 'Email',
            self::CHANNEL_SMS => 'SMS',
            self::CHANNEL_WHATSAPP => 'WhatsApp',
            self::CHANNEL_DIRECT => 'Direct',
            self::CHANNEL_API => 'API',
            self::CHANNEL_MANUAL => 'Manual',
        ];
    }
}
