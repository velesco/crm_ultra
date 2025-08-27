<?php

namespace App\Events;

use App\Models\WhatsAppMessage;
use App\Models\Contact;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppMessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WhatsAppMessage $message;
    public ?Contact $contact;
    public array $messageData;

    /**
     * Create a new event instance.
     */
    public function __construct(WhatsAppMessage $message, ?Contact $contact = null, array $messageData = [])
    {
        $this->message = $message;
        $this->contact = $contact;
        $this->messageData = $messageData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('whatsapp-messages'),
            new PrivateChannel('contact.' . ($this->contact ? $this->contact->id : 'unknown')),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.received';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'phone_number' => $this->message->phone_number,
                'message' => $this->message->message,
                'message_type' => $this->message->message_type,
                'status' => $this->message->status,
                'created_at' => $this->message->created_at->toISOString(),
            ],
            'contact' => $this->contact ? [
                'id' => $this->contact->id,
                'first_name' => $this->contact->first_name,
                'last_name' => $this->contact->last_name,
                'phone' => $this->contact->phone,
            ] : null,
            'metadata' => $this->messageData,
        ];
    }
}
