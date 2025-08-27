<?php

namespace App\Listeners;

use App\Events\ContactCreated;
use App\Jobs\SendEmailCampaignJob;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Handle the event.
     */
    public function handle(ContactCreated $event): void
    {
        try {
            $contact = $event->contact;
            
            // Skip if contact doesn't have email or opted out
            if (!$contact->email || $contact->email_opt_out) {
                return;
            }

            // Skip if source is import (to avoid spam)
            if ($event->source === 'import') {
                return;
            }

            // Get welcome email template
            $welcomeTemplate = EmailTemplate::where('slug', 'welcome-email')
                ->where('is_active', true)
                ->first();

            if (!$welcomeTemplate) {
                Log::warning('Welcome email template not found', [
                    'contact_id' => $contact->id,
                    'template_slug' => 'welcome-email'
                ]);
                return;
            }

            // Prepare email data with personalization
            $emailData = $this->prepareEmailData($contact, $welcomeTemplate, $event);

            // Send welcome email
            $this->emailService->sendSingleEmail(
                email: $contact->email,
                subject: $emailData['subject'],
                htmlBody: $emailData['html_body'],
                textBody: $emailData['text_body'] ?? null,
                metadata: [
                    'type' => 'welcome_email',
                    'contact_id' => $contact->id,
                    'template_id' => $welcomeTemplate->id,
                    'source' => $event->source,
                    'automated' => true,
                ],
                trackingEnabled: true
            );

            Log::info('Welcome email sent successfully', [
                'contact_id' => $contact->id,
                'email' => $contact->email,
                'template_id' => $welcomeTemplate->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'error' => $e->getMessage(),
                'contact_id' => $event->contact->id,
                'contact_email' => $event->contact->email,
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't retry failed welcome emails to avoid spam
            $this->fail($e);
        }
    }

    /**
     * Prepare email data with personalization.
     */
    private function prepareEmailData($contact, $welcomeTemplate, $event): array
    {
        $variables = [
            'first_name' => $contact->first_name ?? 'there',
            'last_name' => $contact->last_name ?? '',
            'full_name' => trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')) ?: 'there',
            'email' => $contact->email,
            'company' => $contact->company ?? '',
            'phone' => $contact->phone ?? '',
            'website' => config('app.url'),
            'company_name' => config('app.name'),
            'current_year' => date('Y'),
            'current_date' => now()->format('F j, Y'),
            'source' => $event->source ?? 'website',
        ];

        // Replace variables in subject
        $subject = $welcomeTemplate->subject;
        foreach ($variables as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }

        // Replace variables in HTML body
        $htmlBody = $welcomeTemplate->html_body;
        foreach ($variables as $key => $value) {
            $htmlBody = str_replace('{{' . $key . '}}', $value, $htmlBody);
        }

        // Replace variables in text body if available
        $textBody = null;
        if ($welcomeTemplate->text_body) {
            $textBody = $welcomeTemplate->text_body;
            foreach ($variables as $key => $value) {
                $textBody = str_replace('{{' . $key . '}}', $value, $textBody);
            }
        }

        return [
            'subject' => $subject,
            'html_body' => $htmlBody,
            'text_body' => $textBody,
            'variables' => $variables,
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(ContactCreated $event, $exception): void
    {
        Log::error('SendWelcomeEmail listener failed permanently', [
            'contact_id' => $event->contact->id,
            'contact_email' => $event->contact->email,
            'exception' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}
