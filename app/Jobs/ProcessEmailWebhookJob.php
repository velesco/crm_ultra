<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\Contact;
use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessEmailWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job should run.
     */
    public $timeout = 60;

    /**
     * The webhook payload.
     */
    protected array $payload;

    /**
     * The webhook provider (sendgrid, mailgun, ses, etc.).
     */
    protected string $provider;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload, string $provider = 'sendgrid')
    {
        $this->payload = $payload;
        $this->provider = $provider;
        $this->onQueue('email-webhooks');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Processing email webhook from {$this->provider}", [
                'payload_size' => count($this->payload)
            ]);

            switch ($this->provider) {
                case 'sendgrid':
                    $this->processSendGridWebhook();
                    break;

                case 'mailgun':
                    $this->processMailgunWebhook();
                    break;

                case 'ses':
                    $this->processSESWebhook();
                    break;

                case 'postmark':
                    $this->processPostmarkWebhook();
                    break;

                default:
                    Log::warning("Unsupported email webhook provider: {$this->provider}");
                    break;
            }

        } catch (Exception $e) {
            Log::error("ProcessEmailWebhookJob failed: " . $e->getMessage(), [
                'provider' => $this->provider,
                'payload_keys' => array_keys($this->payload)
            ]);
            throw $e;
        }
    }

    /**
     * Process SendGrid webhook.
     */
    protected function processSendGridWebhook(): void
    {
        // SendGrid sends an array of events
        $events = is_array($this->payload) && isset($this->payload[0]) ? $this->payload : [$this->payload];

        foreach ($events as $event) {
            $this->processSendGridEvent($event);
        }
    }

    /**
     * Process individual SendGrid event.
     */
    protected function processSendGridEvent(array $event): void
    {
        $eventType = $event['event'] ?? null;
        $email = $event['email'] ?? null;
        $timestamp = $event['timestamp'] ?? time();
        $messageId = $event['sg_message_id'] ?? $event['smtp-id'] ?? null;

        if (!$eventType || !$email) {
            Log::warning("Invalid SendGrid event - missing event type or email");
            return;
        }

        // Extract custom headers for tracking
        $campaignId = $event['campaign_id'] ?? $event['unique_args']['campaign_id'] ?? null;
        $contactId = $event['contact_id'] ?? $event['unique_args']['contact_id'] ?? null;

        // Find email log entry
        $emailLog = $this->findEmailLog($messageId, $email, $campaignId, $contactId);

        if (!$emailLog) {
            Log::debug("Email log not found for SendGrid event: {$eventType} - {$email}");
            return;
        }

        // Process event based on type
        switch ($eventType) {
            case 'delivered':
                $emailLog->update([
                    'status' => 'delivered',
                    'delivered_at' => date('Y-m-d H:i:s', $timestamp),
                ]);
                $this->updateCampaignStats($emailLog->campaign_id, 'delivered');
                break;

            case 'open':
                $this->recordEmailOpen($emailLog, $event, $timestamp);
                break;

            case 'click':
                $this->recordEmailClick($emailLog, $event, $timestamp);
                break;

            case 'bounce':
                $this->recordEmailBounce($emailLog, $event, $timestamp);
                break;

            case 'dropped':
                $emailLog->update([
                    'status' => 'failed',
                    'error_message' => $event['reason'] ?? 'Email dropped by provider',
                ]);
                $this->updateCampaignStats($emailLog->campaign_id, 'failed');
                break;

            case 'unsubscribe':
                $this->recordUnsubscribe($emailLog, $timestamp);
                break;

            case 'spamreport':
                $this->recordSpamReport($emailLog, $timestamp);
                break;

            default:
                Log::debug("Unhandled SendGrid event type: {$eventType}");
                break;
        }
    }

    /**
     * Process Mailgun webhook.
     */
    protected function processMailgunWebhook(): void
    {
        $eventData = $this->payload['event-data'] ?? $this->payload;
        $eventType = $eventData['event'] ?? null;
        $recipient = $eventData['recipient'] ?? null;
        $timestamp = $eventData['timestamp'] ?? time();
        $messageId = $eventData['message']['headers']['message-id'] ?? null;

        if (!$eventType || !$recipient) {
            Log::warning("Invalid Mailgun event - missing event type or recipient");
            return;
        }

        // Extract tracking data
        $campaignId = $eventData['user-variables']['campaign_id'] ?? null;
        $contactId = $eventData['user-variables']['contact_id'] ?? null;

        // Find email log entry
        $emailLog = $this->findEmailLog($messageId, $recipient, $campaignId, $contactId);

        if (!$emailLog) {
            Log::debug("Email log not found for Mailgun event: {$eventType} - {$recipient}");
            return;
        }

        // Process event
        switch ($eventType) {
            case 'delivered':
                $emailLog->update([
                    'status' => 'delivered',
                    'delivered_at' => date('Y-m-d H:i:s', $timestamp),
                ]);
                $this->updateCampaignStats($emailLog->campaign_id, 'delivered');
                break;

            case 'opened':
                $this->recordEmailOpen($emailLog, $eventData, $timestamp);
                break;

            case 'clicked':
                $this->recordEmailClick($emailLog, $eventData, $timestamp);
                break;

            case 'failed':
            case 'rejected':
                $this->recordEmailBounce($emailLog, $eventData, $timestamp);
                break;

            case 'unsubscribed':
                $this->recordUnsubscribe($emailLog, $timestamp);
                break;

            case 'complained':
                $this->recordSpamReport($emailLog, $timestamp);
                break;
        }
    }

    /**
     * Process AWS SES webhook.
     */
    protected function processSESWebhook(): void
    {
        $message = json_decode($this->payload['Message'] ?? '{}', true);
        $eventType = $message['eventType'] ?? null;
        $mail = $message['mail'] ?? [];
        $timestamp = strtotime($mail['timestamp'] ?? 'now');

        if (!$eventType) {
            Log::warning("Invalid SES event - missing event type");
            return;
        }

        $messageId = $mail['messageId'] ?? null;
        $recipients = $mail['commonHeaders']['to'] ?? [];

        foreach ($recipients as $recipient) {
            $emailLog = $this->findEmailLog($messageId, $recipient);

            if (!$emailLog) {
                continue;
            }

            switch ($eventType) {
                case 'send':
                    $emailLog->update([
                        'status' => 'sent',
                        'sent_at' => date('Y-m-d H:i:s', $timestamp),
                    ]);
                    break;

                case 'delivery':
                    $emailLog->update([
                        'status' => 'delivered',
                        'delivered_at' => date('Y-m-d H:i:s', $timestamp),
                    ]);
                    $this->updateCampaignStats($emailLog->campaign_id, 'delivered');
                    break;

                case 'bounce':
                    $bounce = $message['bounce'] ?? [];
                    $this->recordEmailBounce($emailLog, $bounce, $timestamp);
                    break;

                case 'complaint':
                    $this->recordSpamReport($emailLog, $timestamp);
                    break;
            }
        }
    }

    /**
     * Process Postmark webhook.
     */
    protected function processPostmarkWebhook(): void
    {
        $recordType = $this->payload['RecordType'] ?? null;
        $email = $this->payload['Email'] ?? null;
        $messageId = $this->payload['MessageID'] ?? null;

        if (!$recordType || !$email) {
            Log::warning("Invalid Postmark event - missing record type or email");
            return;
        }

        $emailLog = $this->findEmailLog($messageId, $email);

        if (!$emailLog) {
            return;
        }

        switch ($recordType) {
            case 'Delivery':
                $emailLog->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);
                $this->updateCampaignStats($emailLog->campaign_id, 'delivered');
                break;

            case 'Open':
                $this->recordEmailOpen($emailLog, $this->payload, time());
                break;

            case 'Click':
                $this->recordEmailClick($emailLog, $this->payload, time());
                break;

            case 'Bounce':
                $this->recordEmailBounce($emailLog, $this->payload, time());
                break;

            case 'SpamComplaint':
                $this->recordSpamReport($emailLog, time());
                break;
        }
    }

    /**
     * Find email log entry by various identifiers.
     */
    protected function findEmailLog(?string $messageId, string $email, ?string $campaignId = null, ?string $contactId = null): ?EmailLog
    {
        $query = EmailLog::query();

        // Try to find by message ID first
        if ($messageId) {
            $log = $query->where('message_id', $messageId)->first();
            if ($log) return $log;
        }

        // Try to find by email and campaign
        if ($campaignId) {
            $log = EmailLog::where('campaign_id', $campaignId)
                ->where('email', $email)
                ->first();
            if ($log) return $log;
        }

        // Try to find by email and contact
        if ($contactId) {
            $log = EmailLog::where('contact_id', $contactId)
                ->where('email', $email)
                ->first();
            if ($log) return $log;
        }

        // Last resort - find by email and recent timestamp
        return EmailLog::where('email', $email)
            ->where('created_at', '>', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Record email open event.
     */
    protected function recordEmailOpen(EmailLog $emailLog, array $eventData, int $timestamp): void
    {
        $emailLog->increment('opens');
        
        if (!$emailLog->opened_at) {
            $emailLog->update([
                'status' => 'opened',
                'opened_at' => date('Y-m-d H:i:s', $timestamp),
            ]);
            
            $this->updateCampaignStats($emailLog->campaign_id, 'opened');
            $this->updateContactEngagement($emailLog->contact_id, 'email_opened');
        }

        // Store additional open data
        $emailLog->update([
            'last_opened_at' => date('Y-m-d H:i:s', $timestamp),
            'open_details' => array_merge($emailLog->open_details ?? [], [[
                'timestamp' => $timestamp,
                'user_agent' => $eventData['useragent'] ?? $eventData['user-agent'] ?? null,
                'ip' => $eventData['ip'] ?? null,
                'location' => $eventData['geo'] ?? null,
            ]])
        ]);

        Log::debug("Recorded email open for {$emailLog->email}");
    }

    /**
     * Record email click event.
     */
    protected function recordEmailClick(EmailLog $emailLog, array $eventData, int $timestamp): void
    {
        $emailLog->increment('clicks');
        
        if (!$emailLog->clicked_at) {
            $emailLog->update([
                'status' => 'clicked',
                'clicked_at' => date('Y-m-d H:i:s', $timestamp),
            ]);
            
            $this->updateCampaignStats($emailLog->campaign_id, 'clicked');
            $this->updateContactEngagement($emailLog->contact_id, 'email_clicked');
        }

        // Store click details
        $url = $eventData['url'] ?? $eventData['OriginalLink'] ?? null;
        $emailLog->update([
            'last_clicked_at' => date('Y-m-d H:i:s', $timestamp),
            'click_details' => array_merge($emailLog->click_details ?? [], [[
                'timestamp' => $timestamp,
                'url' => $url,
                'user_agent' => $eventData['useragent'] ?? $eventData['user-agent'] ?? null,
                'ip' => $eventData['ip'] ?? null,
            ]])
        ]);

        Log::debug("Recorded email click for {$emailLog->email} on URL: {$url}");
    }

    /**
     * Record email bounce event.
     */
    protected function recordEmailBounce(EmailLog $emailLog, array $eventData, int $timestamp): void
    {
        $bounceType = $eventData['bounce']['bounceType'] ?? $eventData['Type'] ?? 'hard';
        $reason = $eventData['bounce']['bouncedRecipients'][0]['diagnosticCode'] ?? 
                 $eventData['reason'] ?? 
                 $eventData['Description'] ?? 
                 'Email bounced';

        $emailLog->update([
            'status' => 'bounced',
            'bounced_at' => date('Y-m-d H:i:s', $timestamp),
            'bounce_type' => $bounceType,
            'error_message' => $reason,
        ]);

        $this->updateCampaignStats($emailLog->campaign_id, 'bounced');

        // If hard bounce, mark contact as bounced
        if (strtolower($bounceType) === 'hard' && $emailLog->contact_id) {
            Contact::where('id', $emailLog->contact_id)->update([
                'email_bounced' => true,
                'email_bounced_at' => now(),
            ]);
        }

        Log::info("Recorded email bounce for {$emailLog->email}: {$bounceType} - {$reason}");
    }

    /**
     * Record unsubscribe event.
     */
    protected function recordUnsubscribe(EmailLog $emailLog, int $timestamp): void
    {
        $emailLog->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => date('Y-m-d H:i:s', $timestamp),
        ]);

        $this->updateCampaignStats($emailLog->campaign_id, 'unsubscribed');

        // Mark contact as unsubscribed
        if ($emailLog->contact_id) {
            Contact::where('id', $emailLog->contact_id)->update([
                'is_unsubscribed' => true,
                'unsubscribed_at' => now(),
            ]);
        }

        Log::info("Recorded unsubscribe for {$emailLog->email}");
    }

    /**
     * Record spam report event.
     */
    protected function recordSpamReport(EmailLog $emailLog, int $timestamp): void
    {
        $emailLog->update([
            'status' => 'spam_reported',
            'spam_reported_at' => date('Y-m-d H:i:s', $timestamp),
        ]);

        $this->updateCampaignStats($emailLog->campaign_id, 'spam_reported');

        // Mark contact with spam flag
        if ($emailLog->contact_id) {
            Contact::where('id', $emailLog->contact_id)->update([
                'marked_as_spam' => true,
                'marked_as_spam_at' => now(),
            ]);
        }

        Log::warning("Recorded spam report for {$emailLog->email}");
    }

    /**
     * Update campaign statistics.
     */
    protected function updateCampaignStats(?int $campaignId, string $eventType): void
    {
        if (!$campaignId) return;

        $campaign = EmailCampaign::find($campaignId);
        if (!$campaign) return;

        switch ($eventType) {
            case 'delivered':
                $campaign->increment('emails_delivered');
                break;
            case 'opened':
                $campaign->increment('emails_opened');
                break;
            case 'clicked':
                $campaign->increment('emails_clicked');
                break;
            case 'bounced':
                $campaign->increment('emails_bounced');
                break;
            case 'unsubscribed':
                $campaign->increment('emails_unsubscribed');
                break;
            case 'spam_reported':
                $campaign->increment('emails_spam_reported');
                break;
            case 'failed':
                $campaign->increment('emails_failed');
                break;
        }
    }

    /**
     * Update contact engagement score.
     */
    protected function updateContactEngagement(?int $contactId, string $action): void
    {
        if (!$contactId) return;

        $contact = Contact::find($contactId);
        if (!$contact) return;

        $engagementScore = $contact->engagement_score ?? 0;

        switch ($action) {
            case 'email_opened':
                $engagementScore += 1;
                break;
            case 'email_clicked':
                $engagementScore += 3;
                break;
        }

        $contact->update([
            'engagement_score' => min($engagementScore, 100), // Cap at 100
            'last_engaged_at' => now(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("ProcessEmailWebhookJob permanently failed: " . $exception->getMessage(), [
            'provider' => $this->provider,
            'payload_keys' => array_keys($this->payload)
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 180]; // 30 seconds, 1 minute, 3 minutes
    }
}
