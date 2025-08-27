<?php

namespace App\Listeners;

use App\Events\WhatsAppMessageReceived;
use App\Events\EmailOpened;
use App\Events\EmailClicked;
use App\Events\SmsDelivered;
use App\Events\ContactCreated;
use App\Events\ContactUpdated;
use App\Models\Contact;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogCommunication implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        try {
            $communicationData = $this->prepareCommunicationData($event);
            
            if (empty($communicationData)) {
                return;
            }

            // Log the communication
            DB::table('communication_logs')->insert(array_merge($communicationData, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            // Update communication statistics
            $this->updateCommunicationStats($event, $communicationData);

            Log::debug('Communication logged successfully', [
                'type' => $communicationData['type'],
                'contact_id' => $communicationData['contact_id'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log communication', [
                'error' => $e->getMessage(),
                'event' => get_class($event),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Prepare communication data from event.
     */
    private function prepareCommunicationData($event): array
    {
        if ($event instanceof WhatsAppMessageReceived) {
            return [
                'contact_id' => $event->contact?->id,
                'type' => 'whatsapp',
                'direction' => 'inbound',
                'channel' => 'whatsapp',
                'content' => $event->message->message,
                'phone_number' => $event->message->phone_number,
                'message_type' => $event->message->message_type,
                'status' => $event->message->status,
                'external_id' => $event->message->id,
                'metadata' => json_encode([
                    'whatsapp_message_id' => $event->message->id,
                    'session_id' => $event->message->session_id,
                    'message_data' => $event->messageData,
                ]),
            ];
        }

        if ($event instanceof EmailOpened) {
            return [
                'contact_id' => $event->contact->id,
                'type' => 'email',
                'direction' => 'tracking',
                'channel' => 'email',
                'content' => 'Email opened: ' . $event->emailLog->subject,
                'email' => $event->contact->email,
                'subject' => $event->emailLog->subject,
                'external_id' => $event->emailLog->id,
                'metadata' => json_encode([
                    'email_log_id' => $event->emailLog->id,
                    'campaign_id' => $event->campaign?->id,
                    'tracking_data' => $event->trackingData,
                    'open_count' => ($event->emailLog->open_count ?? 0) + 1,
                ]),
            ];
        }

        if ($event instanceof EmailClicked) {
            return [
                'contact_id' => $event->contact->id,
                'type' => 'email',
                'direction' => 'tracking',
                'channel' => 'email',
                'content' => 'Email link clicked: ' . $event->clickedUrl,
                'email' => $event->contact->email,
                'subject' => $event->emailLog->subject,
                'external_id' => $event->emailLog->id,
                'metadata' => json_encode([
                    'email_log_id' => $event->emailLog->id,
                    'campaign_id' => $event->campaign?->id,
                    'clicked_url' => $event->clickedUrl,
                    'tracking_data' => $event->trackingData,
                    'click_count' => ($event->emailLog->click_count ?? 0) + 1,
                ]),
            ];
        }

        if ($event instanceof SmsDelivered) {
            return [
                'contact_id' => $event->contact?->id,
                'type' => 'sms',
                'direction' => 'outbound',
                'channel' => 'sms',
                'content' => $event->smsMessage->message,
                'phone_number' => $event->smsMessage->phone_number,
                'status' => 'delivered',
                'external_id' => $event->smsMessage->id,
                'metadata' => json_encode([
                    'sms_message_id' => $event->smsMessage->id,
                    'provider_id' => $event->smsMessage->provider_id,
                    'cost' => $event->smsMessage->cost,
                    'delivery_data' => $event->deliveryData,
                ]),
            ];
        }

        if ($event instanceof ContactCreated) {
            return [
                'contact_id' => $event->contact->id,
                'type' => 'system',
                'direction' => 'internal',
                'channel' => 'system',
                'content' => 'Contact created',
                'email' => $event->contact->email,
                'phone_number' => $event->contact->phone,
                'metadata' => json_encode([
                    'source' => $event->source,
                    'contact_data' => $event->metadata,
                    'action' => 'created',
                ]),
            ];
        }

        if ($event instanceof ContactUpdated) {
            return [
                'contact_id' => $event->contact->id,
                'type' => 'system',
                'direction' => 'internal',
                'channel' => 'system',
                'content' => 'Contact updated',
                'email' => $event->contact->email,
                'phone_number' => $event->contact->phone,
                'metadata' => json_encode([
                    'changes' => $event->changes,
                    'original' => $event->original,
                    'action' => 'updated',
                ]),
            ];
        }

        return [];
    }

    /**
     * Update communication statistics.
     */
    private function updateCommunicationStats($event, array $communicationData): void
    {
        try {
            // Update daily communication stats
            $today = now()->format('Y-m-d');
            $statType = $communicationData['type'] . '_' . $communicationData['direction'];

            DB::table('communication_stats')
                ->updateOrInsert(
                    [
                        'date' => $today,
                        'type' => $statType,
                        'channel' => $communicationData['channel'],
                    ],
                    [
                        'count' => DB::raw('COALESCE(count, 0) + 1'),
                        'updated_at' => now(),
                    ]
                );

            // Update contact communication frequency
            if ($contactId = $communicationData['contact_id']) {
                $contact = Contact::find($contactId);
                if ($contact) {
                    $contact->increment('total_communications');
                    $contact->update(['last_communication' => now()]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to update communication stats', [
                'error' => $e->getMessage(),
                'communication_data' => $communicationData,
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, $exception): void
    {
        Log::error('LogCommunication listener failed', [
            'event' => get_class($event),
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
