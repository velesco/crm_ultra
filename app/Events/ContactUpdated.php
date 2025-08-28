<?php

namespace App\Events;

use App\Models\Contact;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Contact $contact;

    public array $changes;

    public array $original;

    /**
     * Create a new event instance.
     */
    public function __construct(Contact $contact, array $changes = [], array $original = [])
    {
        $this->contact = $contact;
        $this->changes = $changes;
        $this->original = $original;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('contacts'),
            new PrivateChannel('contact.'.$this->contact->id),
            new PrivateChannel('dashboard'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'contact.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'contact' => [
                'id' => $this->contact->id,
                'first_name' => $this->contact->first_name,
                'last_name' => $this->contact->last_name,
                'email' => $this->contact->email,
                'phone' => $this->contact->phone,
                'status' => $this->contact->status,
                'updated_at' => $this->contact->updated_at->toISOString(),
            ],
            'changes' => $this->changes,
            'original' => $this->original,
        ];
    }
}
