<?php

namespace App\Events;

use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\EmailLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailClicked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public EmailLog $emailLog;

    public Contact $contact;

    public ?EmailCampaign $campaign;

    public string $clickedUrl;

    public array $trackingData;

    /**
     * Create a new event instance.
     */
    public function __construct(EmailLog $emailLog, Contact $contact, string $clickedUrl, ?EmailCampaign $campaign = null, array $trackingData = [])
    {
        $this->emailLog = $emailLog;
        $this->contact = $contact;
        $this->clickedUrl = $clickedUrl;
        $this->campaign = $campaign;
        $this->trackingData = $trackingData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('email-tracking'),
            new PrivateChannel('contact.'.$this->contact->id),
            new PrivateChannel('campaign.'.($this->campaign ? $this->campaign->id : 'single')),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'email.clicked';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'email_log' => [
                'id' => $this->emailLog->id,
                'email' => $this->emailLog->email,
                'subject' => $this->emailLog->subject,
                'clicked_at' => now()->toISOString(),
                'click_count' => $this->emailLog->click_count + 1,
            ],
            'contact' => [
                'id' => $this->contact->id,
                'email' => $this->contact->email,
                'first_name' => $this->contact->first_name,
                'last_name' => $this->contact->last_name,
            ],
            'campaign' => $this->campaign ? [
                'id' => $this->campaign->id,
                'name' => $this->campaign->name,
                'subject' => $this->campaign->subject,
            ] : null,
            'clicked_url' => $this->clickedUrl,
            'tracking_data' => $this->trackingData,
        ];
    }
}
