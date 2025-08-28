<?php

namespace App\Listeners;

use App\Events\EmailClicked;
use App\Events\EmailOpened;
use App\Events\SmsDelivered;
use App\Events\WhatsAppMessageReceived;
use App\Models\Contact;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateContactActivity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle WhatsApp message received event.
     */
    public function handle($event): void
    {
        try {
            $contact = $this->getContactFromEvent($event);

            if (! $contact) {
                return;
            }

            $activityType = $this->getActivityType($event);
            $activityData = $this->getActivityData($event);

            // Update contact's last activity
            $contact->update([
                'last_activity' => now(),
                'engagement_score' => $this->calculateEngagementScore($contact, $activityType),
            ]);

            // Log the activity
            $this->logActivity($contact, $activityType, $activityData);

            // Update contact statistics
            $this->updateContactStats($contact, $activityType);

        } catch (\Exception $e) {
            Log::error('Failed to update contact activity', [
                'error' => $e->getMessage(),
                'event' => get_class($event),
                'contact_id' => $contact->id ?? null,
            ]);
        }
    }

    /**
     * Get contact from event.
     */
    private function getContactFromEvent($event): ?Contact
    {
        if ($event instanceof WhatsAppMessageReceived) {
            return $event->contact;
        }

        if ($event instanceof EmailOpened || $event instanceof EmailClicked) {
            return $event->contact;
        }

        if ($event instanceof SmsDelivered) {
            return $event->contact;
        }

        return null;
    }

    /**
     * Get activity type from event.
     */
    private function getActivityType($event): string
    {
        if ($event instanceof WhatsAppMessageReceived) {
            return 'whatsapp_received';
        }

        if ($event instanceof EmailOpened) {
            return 'email_opened';
        }

        if ($event instanceof EmailClicked) {
            return 'email_clicked';
        }

        if ($event instanceof SmsDelivered) {
            return 'sms_delivered';
        }

        return 'unknown';
    }

    /**
     * Get activity data from event.
     */
    private function getActivityData($event): array
    {
        if ($event instanceof WhatsAppMessageReceived) {
            return [
                'message_id' => $event->message->id,
                'phone_number' => $event->message->phone_number,
                'message_type' => $event->message->message_type,
            ];
        }

        if ($event instanceof EmailOpened) {
            return [
                'email_log_id' => $event->emailLog->id,
                'campaign_id' => $event->campaign?->id,
                'subject' => $event->emailLog->subject,
                'tracking_data' => $event->trackingData,
            ];
        }

        if ($event instanceof EmailClicked) {
            return [
                'email_log_id' => $event->emailLog->id,
                'campaign_id' => $event->campaign?->id,
                'clicked_url' => $event->clickedUrl,
                'tracking_data' => $event->trackingData,
            ];
        }

        if ($event instanceof SmsDelivered) {
            return [
                'sms_message_id' => $event->smsMessage->id,
                'phone_number' => $event->smsMessage->phone_number,
                'delivery_data' => $event->deliveryData,
            ];
        }

        return [];
    }

    /**
     * Calculate engagement score based on activity.
     */
    private function calculateEngagementScore(Contact $contact, string $activityType): int
    {
        $currentScore = $contact->engagement_score ?? 0;

        $scoreIncrements = [
            'whatsapp_received' => 10,
            'email_opened' => 5,
            'email_clicked' => 15,
            'sms_delivered' => 8,
        ];

        $increment = $scoreIncrements[$activityType] ?? 0;

        // Cap the engagement score at 100
        return min(100, $currentScore + $increment);
    }

    /**
     * Log the activity.
     */
    private function logActivity(Contact $contact, string $activityType, array $activityData): void
    {
        DB::table('contact_activities')->insert([
            'contact_id' => $contact->id,
            'activity_type' => $activityType,
            'activity_data' => json_encode($activityData),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Update contact statistics.
     */
    private function updateContactStats(Contact $contact, string $activityType): void
    {
        $statsColumn = match ($activityType) {
            'whatsapp_received' => 'whatsapp_messages_received',
            'email_opened' => 'emails_opened',
            'email_clicked' => 'emails_clicked',
            'sms_delivered' => 'sms_messages_received',
            default => null,
        };

        if ($statsColumn && $contact->getConnection()->getSchemaBuilder()->hasColumn('contacts', $statsColumn)) {
            $contact->increment($statsColumn);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, $exception): void
    {
        Log::error('UpdateContactActivity listener failed', [
            'event' => get_class($event),
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
