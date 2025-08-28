<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\ContactSegment;
use App\Models\SmsMessage;
use App\Services\SmsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job should run.
     */
    public $timeout = 300;

    /**
     * The SMS message instance.
     */
    protected SmsMessage $smsMessage;

    /**
     * Target recipients (contacts, segments, or phone numbers).
     */
    protected array $recipients;

    /**
     * Recipients type (contacts, segments, or phones).
     */
    protected string $recipientsType;

    /**
     * Create a new job instance.
     */
    public function __construct(SmsMessage $smsMessage, array $recipients, string $recipientsType = 'contacts')
    {
        $this->smsMessage = $smsMessage;
        $this->recipients = $recipients;
        $this->recipientsType = $recipientsType;

        // Set queue based on urgency
        $this->onQueue($smsMessage->is_urgent ? 'sms-urgent' : 'sms');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Check if SMS is still pending
            if ($this->smsMessage->status !== 'pending') {
                Log::info("SMS message {$this->smsMessage->id} is not pending, current status: {$this->smsMessage->status}");

                return;
            }

            // Update status to sending
            $this->smsMessage->update([
                'status' => 'sending',
                'sent_at' => now(),
            ]);

            Log::info("Starting bulk SMS send for message ID: {$this->smsMessage->id}");

            // Get SMS Service
            $smsService = app(SmsService::class);

            // Get list of phone numbers to send to
            $phoneNumbers = $this->getPhoneNumbers();

            if (empty($phoneNumbers)) {
                throw new Exception('No valid phone numbers found for SMS sending');
            }

            $successCount = 0;
            $failureCount = 0;
            $errors = [];

            // Send SMS to each phone number
            foreach ($phoneNumbers as $phoneData) {
                try {
                    // Personalize message content
                    $personalizedMessage = $this->personalizeMessage($phoneData);

                    // Prepare SMS data
                    $smsData = [
                        'to' => $phoneData['phone'],
                        'message' => $personalizedMessage,
                        'provider_id' => $this->smsMessage->provider_id,
                        'contact_id' => $phoneData['contact_id'] ?? null,
                        'campaign_name' => $this->smsMessage->campaign_name,
                        'is_urgent' => $this->smsMessage->is_urgent,
                        'schedule_at' => $this->smsMessage->schedule_at,
                    ];

                    // Send the SMS
                    $result = $smsService->sendSms($smsData);

                    if ($result['success']) {
                        $successCount++;

                        // Update contact last_contacted if contact exists
                        if (! empty($phoneData['contact_id'])) {
                            Contact::where('id', $phoneData['contact_id'])
                                ->update(['last_contacted_at' => now()]);
                        }

                        Log::debug("SMS sent successfully to {$phoneData['phone']}");
                    } else {
                        $failureCount++;
                        $errors[] = "Failed to send to {$phoneData['phone']}: ".$result['error'];
                        Log::warning("Failed to send SMS to {$phoneData['phone']}: ".$result['error']);
                    }

                } catch (Exception $e) {
                    $failureCount++;
                    $errors[] = "Error sending to {$phoneData['phone']}: ".$e->getMessage();
                    Log::error("Error sending SMS to {$phoneData['phone']}: ".$e->getMessage());
                }

                // Add small delay between sends to respect rate limits
                if ($this->smsMessage->provider && $this->smsMessage->provider->rate_limit_per_minute > 0) {
                    $delayMs = (60 / $this->smsMessage->provider->rate_limit_per_minute) * 1000;
                    usleep($delayMs * 1000); // Convert to microseconds
                }
            }

            // Update SMS message with final status and statistics
            $finalStatus = $failureCount === 0 ? 'sent' : ($successCount === 0 ? 'failed' : 'partially_sent');

            $this->smsMessage->update([
                'status' => $finalStatus,
                'sent_count' => $successCount,
                'failed_count' => $failureCount,
                'total_recipients' => count($phoneNumbers),
                'delivery_report' => [
                    'success_count' => $successCount,
                    'failure_count' => $failureCount,
                    'errors' => array_slice($errors, 0, 50), // Limit errors stored
                    'sent_at' => now()->toISOString(),
                ],
                'completed_at' => now(),
            ]);

            Log::info("Bulk SMS completed for message {$this->smsMessage->id}. Success: {$successCount}, Failed: {$failureCount}");

        } catch (Exception $e) {
            Log::error("SendBulkSmsJob failed for message {$this->smsMessage->id}: ".$e->getMessage());

            // Update SMS status to failed
            $this->smsMessage->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }

    /**
     * Get phone numbers based on recipients type.
     */
    protected function getPhoneNumbers(): array
    {
        $phoneNumbers = [];

        switch ($this->recipientsType) {
            case 'contacts':
                $phoneNumbers = $this->getPhoneNumbersFromContacts();
                break;

            case 'segments':
                $phoneNumbers = $this->getPhoneNumbersFromSegments();
                break;

            case 'phones':
                $phoneNumbers = $this->getPhoneNumbersFromList();
                break;

            default:
                throw new Exception("Invalid recipients type: {$this->recipientsType}");
        }

        // Filter out invalid phone numbers
        return array_filter($phoneNumbers, function ($phoneData) {
            return ! empty($phoneData['phone']) && $this->isValidPhoneNumber($phoneData['phone']);
        });
    }

    /**
     * Get phone numbers from contact IDs.
     */
    protected function getPhoneNumbersFromContacts(): array
    {
        $contacts = Contact::whereIn('id', $this->recipients)
            ->where('is_active', true)
            ->where('is_unsubscribed', false)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get(['id', 'phone', 'first_name', 'last_name', 'name', 'company']);

        return $contacts->map(function ($contact) {
            return [
                'contact_id' => $contact->id,
                'phone' => $contact->phone,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'name' => $contact->name,
                'company' => $contact->company,
            ];
        })->toArray();
    }

    /**
     * Get phone numbers from segment IDs.
     */
    protected function getPhoneNumbersFromSegments(): array
    {
        $phoneNumbers = [];

        foreach ($this->recipients as $segmentId) {
            $segment = ContactSegment::find($segmentId);
            if (! $segment) {
                Log::warning("Segment {$segmentId} not found for SMS campaign");

                continue;
            }

            $contacts = $segment->contacts()
                ->where('is_active', true)
                ->where('is_unsubscribed', false)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get(['id', 'phone', 'first_name', 'last_name', 'name', 'company']);

            foreach ($contacts as $contact) {
                $phoneNumbers[] = [
                    'contact_id' => $contact->id,
                    'phone' => $contact->phone,
                    'first_name' => $contact->first_name,
                    'last_name' => $contact->last_name,
                    'name' => $contact->name,
                    'company' => $contact->company,
                    'segment_id' => $segmentId,
                    'segment_name' => $segment->name,
                ];
            }
        }

        // Remove duplicates by phone number
        $uniquePhones = [];
        $result = [];
        foreach ($phoneNumbers as $phoneData) {
            if (! in_array($phoneData['phone'], $uniquePhones)) {
                $uniquePhones[] = $phoneData['phone'];
                $result[] = $phoneData;
            }
        }

        return $result;
    }

    /**
     * Get phone numbers from raw phone list.
     */
    protected function getPhoneNumbersFromList(): array
    {
        return array_map(function ($phone) {
            return [
                'contact_id' => null,
                'phone' => $phone,
                'first_name' => '',
                'last_name' => '',
                'name' => '',
                'company' => '',
            ];
        }, $this->recipients);
    }

    /**
     * Personalize message content with contact data.
     */
    protected function personalizeMessage(array $phoneData): string
    {
        $message = $this->smsMessage->message;

        $replacements = [
            '{{first_name}}' => $phoneData['first_name'] ?? '',
            '{{last_name}}' => $phoneData['last_name'] ?? '',
            '{{name}}' => $phoneData['name'] ?? '',
            '{{full_name}}' => $phoneData['name'] ?? '',
            '{{company}}' => $phoneData['company'] ?? '',
            '{{phone}}' => $phoneData['phone'] ?? '',
            '{{campaign_name}}' => $this->smsMessage->campaign_name ?? '',
            '{{sender_name}}' => $this->smsMessage->sender_name ?? config('app.name'),
            '{{current_date}}' => now()->format('F j, Y'),
            '{{current_time}}' => now()->format('g:i A'),
            '{{unsubscribe_link}}' => $this->generateUnsubscribeLink($phoneData),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * Generate unsubscribe link for SMS.
     */
    protected function generateUnsubscribeLink(array $phoneData): string
    {
        if (empty($phoneData['contact_id'])) {
            return route('sms.unsubscribe.phone', [
                'phone' => base64_encode($phoneData['phone']),
                'token' => hash('sha256', $phoneData['phone'].config('app.key')),
            ]);
        }

        return route('sms.unsubscribe.contact', [
            'contact' => $phoneData['contact_id'],
            'token' => hash('sha256', $phoneData['contact_id'].config('app.key')),
        ]);
    }

    /**
     * Validate phone number format.
     */
    protected function isValidPhoneNumber(string $phone): bool
    {
        // Remove all non-numeric characters except +
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);

        // Basic validation: should have at least 10 digits
        if (strlen($cleanPhone) < 10) {
            return false;
        }

        // Should not exceed 15 digits (international standard)
        if (strlen($cleanPhone) > 15) {
            return false;
        }

        // Should start with + for international or be 10+ digits for domestic
        if (str_starts_with($cleanPhone, '+')) {
            return strlen($cleanPhone) >= 11; // + and at least 10 digits
        }

        return true;
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("SendBulkSmsJob permanently failed for message {$this->smsMessage->id}: ".$exception->getMessage());

        $this->smsMessage->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 180, 300]; // 1 minute, 3 minutes, 5 minutes
    }
}
