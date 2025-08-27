<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\Contact;
use App\Models\EmailLog;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SendEmailCampaignJob implements ShouldQueue
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
     * The email campaign instance.
     */
    protected EmailCampaign $campaign;

    /**
     * The contact to send email to.
     */
    protected Contact $contact;

    /**
     * Create a new job instance.
     */
    public function __construct(EmailCampaign $campaign, Contact $contact)
    {
        $this->campaign = $campaign;
        $this->contact = $contact;
        
        // Set queue based on priority
        $this->onQueue($campaign->priority === 'high' ? 'high' : 'emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Check if campaign is still active and not paused
            if (!$this->campaign->isActive() || $this->campaign->status === 'paused') {
                Log::info("Campaign {$this->campaign->id} is not active or paused, skipping email to contact {$this->contact->id}");
                return;
            }

            // Check if contact is still active and not unsubscribed
            if (!$this->contact->is_active || $this->contact->is_unsubscribed) {
                Log::info("Contact {$this->contact->id} is inactive or unsubscribed, skipping email");
                return;
            }

            // Check if email was already sent to this contact for this campaign
            $existingLog = EmailLog::where('campaign_id', $this->campaign->id)
                ->where('contact_id', $this->contact->id)
                ->where('status', 'sent')
                ->first();

            if ($existingLog) {
                Log::info("Email already sent to contact {$this->contact->id} for campaign {$this->campaign->id}");
                return;
            }

            // Get EmailService instance
            $emailService = app(EmailService::class);

            // Prepare email data
            $emailData = [
                'to' => $this->contact->email,
                'to_name' => $this->contact->name,
                'subject' => $this->personalizeContent($this->campaign->subject),
                'html_content' => $this->personalizeContent($this->campaign->content),
                'campaign_id' => $this->campaign->id,
                'contact_id' => $this->contact->id,
                'template_id' => $this->campaign->template_id,
                'tracking_enabled' => true,
                'unsubscribe_url' => route('unsubscribe', [
                    'token' => $this->generateUnsubscribeToken()
                ]),
                'view_in_browser_url' => route('email.view-in-browser', [
                    'campaign' => $this->campaign->id,
                    'contact' => $this->contact->id,
                    'token' => $this->generateViewToken()
                ])
            ];

            // Add personalization variables
            $emailData['personalization'] = [
                'first_name' => $this->contact->first_name,
                'last_name' => $this->contact->last_name,
                'full_name' => $this->contact->name,
                'email' => $this->contact->email,
                'company' => $this->contact->company,
                'phone' => $this->contact->phone,
                'city' => $this->contact->city,
                'country' => $this->contact->country,
            ];

            // Send the email
            $result = $emailService->sendCampaignEmail($emailData);

            if ($result['success']) {
                // Update campaign statistics
                $this->campaign->increment('emails_sent');
                $this->campaign->touch('last_sent_at');

                // Log successful send
                Log::info("Email campaign {$this->campaign->id} sent successfully to {$this->contact->email}");

                // Update contact last_contacted
                $this->contact->update([
                    'last_contacted_at' => now(),
                    'contact_source' => $this->contact->contact_source ?? 'email_campaign'
                ]);

            } else {
                // Log failed send
                Log::error("Failed to send email campaign {$this->campaign->id} to {$this->contact->email}: " . $result['error']);
                
                // Increment failed count
                $this->campaign->increment('emails_failed');
                
                throw new Exception($result['error']);
            }

        } catch (Exception $e) {
            // Log the error
            Log::error("SendEmailCampaignJob failed for campaign {$this->campaign->id} and contact {$this->contact->id}: " . $e->getMessage());

            // Create error log entry
            EmailLog::create([
                'campaign_id' => $this->campaign->id,
                'contact_id' => $this->contact->id,
                'email' => $this->contact->email,
                'subject' => $this->campaign->subject,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'sent_at' => now()
            ]);

            // Increment failed count if not already done
            if (!str_contains($e->getMessage(), 'Failed to send email')) {
                $this->campaign->increment('emails_failed');
            }

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Personalize content with contact data.
     */
    protected function personalizeContent(string $content): string
    {
        $replacements = [
            '{{first_name}}' => $this->contact->first_name ?? '',
            '{{last_name}}' => $this->contact->last_name ?? '',
            '{{full_name}}' => $this->contact->name ?? '',
            '{{name}}' => $this->contact->name ?? '',
            '{{email}}' => $this->contact->email ?? '',
            '{{company}}' => $this->contact->company ?? '',
            '{{phone}}' => $this->contact->phone ?? '',
            '{{city}}' => $this->contact->city ?? '',
            '{{country}}' => $this->contact->country ?? '',
            '{{campaign_name}}' => $this->campaign->name ?? '',
            '{{sender_name}}' => $this->campaign->sender_name ?? config('mail.from.name'),
            '{{sender_email}}' => $this->campaign->sender_email ?? config('mail.from.address'),
            '{{current_date}}' => now()->format('F j, Y'),
            '{{current_year}}' => now()->format('Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Generate unsubscribe token for contact.
     */
    protected function generateUnsubscribeToken(): string
    {
        return hash('sha256', $this->contact->email . $this->contact->id . config('app.key'));
    }

    /**
     * Generate view-in-browser token.
     */
    protected function generateViewToken(): string
    {
        return hash('sha256', $this->campaign->id . $this->contact->id . config('app.key'));
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("SendEmailCampaignJob permanently failed for campaign {$this->campaign->id} and contact {$this->contact->id}: " . $exception->getMessage());

        // Create final error log entry
        EmailLog::updateOrCreate(
            [
                'campaign_id' => $this->campaign->id,
                'contact_id' => $this->contact->id,
            ],
            [
                'email' => $this->contact->email,
                'subject' => $this->campaign->subject,
                'status' => 'permanently_failed',
                'error_message' => $exception->getMessage(),
                'sent_at' => now()
            ]
        );

        // Increment permanent failure count
        $this->campaign->increment('emails_permanently_failed');
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 300, 900]; // 1 minute, 5 minutes, 15 minutes
    }
}
