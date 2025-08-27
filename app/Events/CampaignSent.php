<?php

namespace App\Events;

use App\Models\EmailCampaign;
use App\Models\Contact;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampaignSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public EmailCampaign $campaign;
    public int $recipientCount;
    public array $statistics;

    /**
     * Create a new event instance.
     */
    public function __construct(EmailCampaign $campaign, int $recipientCount, array $statistics = [])
    {
        $this->campaign = $campaign;
        $this->recipientCount = $recipientCount;
        $this->statistics = $statistics;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('campaigns'),
            new PrivateChannel('campaign.' . $this->campaign->id),
            new PrivateChannel('dashboard'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'campaign.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'campaign' => [
                'id' => $this->campaign->id,
                'name' => $this->campaign->name,
                'subject' => $this->campaign->subject,
                'status' => $this->campaign->status,
                'sent_at' => $this->campaign->sent_at?->toISOString(),
            ],
            'recipient_count' => $this->recipientCount,
            'statistics' => array_merge([
                'total_recipients' => $this->recipientCount,
                'sent_count' => 0,
                'delivered_count' => 0,
                'failed_count' => 0,
            ], $this->statistics),
        ];
    }
}
