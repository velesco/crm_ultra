<?php

namespace App\Events;

use App\Models\SmsMessage;
use App\Models\Contact;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SmsDelivered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public SmsMessage $smsMessage;
    public ?Contact $contact;
    public array $deliveryData;

    /**
     * Create a new event instance.
     */
    public function __construct(SmsMessage $smsMessage, ?Contact $contact = null, array $deliveryData = [])
    {
        $this->smsMessage = $smsMessage;
        $this->contact = $contact;
        $this->deliveryData = $deliveryData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('sms-messages'),
            new PrivateChannel('contact.' . ($this->contact ? $this->contact->id : 'unknown')),
            new PrivateChannel('dashboard'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'sms.delivered';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'sms_message' => [
                'id' => $this->smsMessage->id,
                'phone_number' => $this->smsMessage->phone_number,
                'message' => $this->smsMessage->message,
                'status' => $this->smsMessage->status,
                'delivered_at' => now()->toISOString(),
                'cost' => $this->smsMessage->cost,
            ],
            'contact' => $this->contact ? [
                'id' => $this->contact->id,
                'first_name' => $this->contact->first_name,
                'last_name' => $this->contact->last_name,
                'phone' => $this->contact->phone,
            ] : null,
            'delivery_data' => $this->deliveryData,
        ];
    }
}
