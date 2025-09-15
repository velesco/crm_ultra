<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\SmtpConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Str;

class EmailService
{
    public function createCampaign(array $data)
    {
        return EmailCampaign::query()->create([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'content' => $data['content'],
            'template_id' => $data['template_id'] ?? null,
            'smtp_config_id' => $data['smtp_config_id'],
            'status' => 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'settings' => $data['settings'] ?? [],
            'created_by' => auth()->id(),
        ]);
    }

    public function addContactsToCampaign(EmailCampaign $campaign, array $contactIds)
    {
        $existingContacts = $campaign->contacts()->pluck('contact_id')->toArray();
        $newContactIds = array_diff($contactIds, $existingContacts);

        if (! empty($newContactIds)) {
            $syncData = [];
            foreach ($newContactIds as $contactId) {
                $syncData[$contactId] = ['status' => 'pending'];
            }

            $campaign->contacts()->attach($syncData);
            $campaign->update(['total_recipients' => $campaign->contacts()->count()]);
        }

        return [
            'success' => true,
            'added' => count($newContactIds),
            'total_recipients' => $campaign->contacts()->count(),
        ];
    }

    public function sendCampaign(EmailCampaign $campaign)
    {
        if ($campaign->status !== 'draft' && $campaign->status !== 'scheduled') {
            return [
                'success' => false,
                'message' => 'Campaign cannot be sent from current status: '.$campaign->status,
            ];
        }

        // Check if scheduled time has arrived
        if ($campaign->scheduled_at && $campaign->scheduled_at->isFuture()) {
            return [
                'success' => false,
                'message' => 'Campaign is scheduled for future delivery',
            ];
        }

        $campaign->update(['status' => 'sending']);

        try {
            $smtpConfig = $campaign->smtpConfig;

            if (! $smtpConfig || ! $smtpConfig->is_active) {
                throw new \Exception('SMTP configuration is not active or not found');
            }

            // Get pending contacts
            $pendingContacts = $campaign->contacts()
                ->wherePivot('status', 'pending')
                ->get();

            if ($pendingContacts->isEmpty()) {
                $campaign->update(['status' => 'sent', 'sent_at' => now()]);

                return [
                    'success' => true,
                    'message' => 'No pending recipients found',
                ];
            }

            $sent = 0;
            $failed = 0;

            foreach ($pendingContacts as $contact) {
                try {
                    $result = $this->sendEmailToContact($campaign, $contact, $smtpConfig);

                    if ($result['success']) {
                        $sent++;
                        $campaign->contacts()->updateExistingPivot($contact->id, [
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);
                    } else {
                        $failed++;
                        $campaign->contacts()->updateExistingPivot($contact->id, [
                            'status' => 'failed',
                            'error_message' => $result['message'],
                        ]);
                    }

                    // Update campaign stats
                    $campaign->increment('sent_count');

                    if ($result['success']) {
                        $campaign->increment('delivered_count');
                    } else {
                        $campaign->increment('failed_count');
                    }

                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Email send error for contact '.$contact->id.': '.$e->getMessage());

                    $campaign->contacts()->updateExistingPivot($contact->id, [
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);

                    $campaign->increment('failed_count');
                }

                // Add small delay to avoid overwhelming the SMTP server
                if ($sent % 10 == 0) {
                    sleep(1);
                }
            }

            $campaign->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => "Campaign sent. Success: {$sent}, Failed: {$failed}",
                'sent' => $sent,
                'failed' => $failed,
            ];

        } catch (\Exception $e) {
            $campaign->update(['status' => 'failed']);

            Log::error('Campaign send error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Campaign send failed: '.$e->getMessage(),
            ];
        }
    }

    protected function sendEmailToContact(EmailCampaign $campaign, Contact $contact, SmtpConfig $smtpConfig)
    {
        try {
            if (! $smtpConfig->canSend()) {
                throw new \Exception('SMTP config has reached sending limits');
            }

            if (empty($contact->email)) {
                throw new \Exception('Contact does not have an email address');
            }

            // Generate tracking ID
            $trackingId = Str::uuid();

            // Prepare email content with personalization
            $variables = [
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'full_name' => $contact->full_name,
                'email' => $contact->email,
                'company' => $contact->company,
                'phone' => $contact->phone,
            ];

            $subject = $this->replaceVariables($campaign->subject, $variables);
            $content = $this->replaceVariables($campaign->content, $variables);

            // Add tracking pixels and unsubscribe links
            $content = $this->addTrackingElements($content, $trackingId);

            // Send email using Laravel's Mail facade with custom SMTP config
            $result = Mail::send([], [], function (Message $message) use ($subject, $content, $contact, $smtpConfig) {
                $message->to($contact->email, $contact->full_name)
                        ->subject($subject)
                        ->from($smtpConfig->from_email, $smtpConfig->from_name);

                // Set both HTML and text content
                $message->setBody($content, 'text/html');
                $textContent = strip_tags($content);
                $message->addPart($textContent, 'text/plain');

                // Configure SMTP for this message
                $transportConfig = [
                    'host' => $smtpConfig->host,
                    'port' => $smtpConfig->port,
                    'encryption' => $smtpConfig->encryption,
                    'username' => $smtpConfig->username,
                    'password' => $smtpConfig->password,
                ];

                // Set custom mailer configuration
                config([
                    'mail.mailers.custom_smtp' => [
                        'transport' => 'smtp',
                        'host' => $smtpConfig->host,
                        'port' => $smtpConfig->port,
                        'encryption' => $smtpConfig->encryption,
                        'username' => $smtpConfig->username,
                        'password' => $smtpConfig->password,
                        'timeout' => null,
                    ]
                ]);
            });

            // Log email
            EmailLog::create([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'smtp_config_id' => $smtpConfig->id,
                'subject' => $subject,
                'content' => $content,
                'to_email' => $contact->email,
                'status' => 'sent',
                'sent_at' => now(),
                'tracking_id' => $trackingId,
            ]);

            // Update SMTP config stats
            $smtpConfig->incrementSent();

            // Create communication record
            $this->createCommunication($contact, $subject, $content);

            return ['success' => true];

        } catch (\Exception $e) {
            // Log failed email
            EmailLog::create([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'smtp_config_id' => $smtpConfig->id,
                'subject' => $subject ?? $campaign->subject,
                'content' => $content ?? $campaign->content,
                'to_email' => $contact->email,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'tracking_id' => $trackingId ?? null,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function replaceVariables(string $content, array $variables)
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{'.$key.'}}', $value ?? '', $content);
        }

        return $content;
    }

    protected function addTrackingElements(string $content, string $trackingId)
    {
        $trackingPixel = '<img src="'.route('email.track.open', $trackingId).'" width="1" height="1" style="display:none;" />';
        $unsubscribeLink = '<p style="font-size: 12px; color: #666; text-align: center; margin-top: 20px;">'
            .'<a href="'.route('email.unsubscribe', $trackingId).'" style="color: #666;">Unsubscribe</a>'
            .'</p>';

        // Add tracking pixel before closing body tag
        if (strpos($content, '</body>') !== false) {
            $content = str_replace('</body>', $trackingPixel.'</body>', $content);
        } else {
            $content .= $trackingPixel;
        }

        // Add unsubscribe link
        if (strpos($content, '</body>') !== false) {
            $content = str_replace('</body>', $unsubscribeLink.'</body>', $content);
        } else {
            $content .= $unsubscribeLink;
        }

        return $content;
    }

    protected function createCommunication(Contact $contact, string $subject, string $content)
    {
        return \App\Models\Communication::create([
            'contact_id' => $contact->id,
            'user_id' => auth()->id() ?? 1,
            'type' => 'email',
            'direction' => 'outbound',
            'subject' => $subject,
            'content' => $content,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function sendSingleEmail(Contact $contact, string $subject, string $content, SmtpConfig $smtpConfig, ?EmailTemplate $template = null)
    {
        try {
            // Create a temporary campaign for single emails
            $campaign = EmailCampaign::create([
                'name' => 'Single Email: '.$subject,
                'subject' => $subject,
                'content' => $content,
                'template_id' => $template?->id,
                'smtp_config_id' => $smtpConfig->id,
                'status' => 'sending',
                'total_recipients' => 1,
                'created_by' => auth()->id(),
            ]);

            // Add contact to campaign
            $campaign->contacts()->attach($contact->id, ['status' => 'pending']);

            // Send email
            $result = $this->sendEmailToContact($campaign, $contact, $smtpConfig);

            // Update campaign status
            if ($result['success']) {
                $campaign->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'sent_count' => 1,
                    'delivered_count' => 1,
                ]);
            } else {
                $campaign->update([
                    'status' => 'failed',
                    'failed_count' => 1,
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Single email send error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to send email: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Send a quick email to a single contact using modern Laravel Mail
     */
    public function sendQuickEmail(Contact $contact, string $subject, string $content, SmtpConfig $smtpConfig)
    {
        try {
            // Validate inputs
            if (empty($contact->email)) {
                return [
                    'success' => false,
                    'message' => 'Contact does not have an email address'
                ];
            }

            if (!$smtpConfig->is_active) {
                return [
                    'success' => false,
                    'message' => 'SMTP configuration is not active'
                ];
            }

            if (!$smtpConfig->canSend()) {
                return [
                    'success' => false,
                    'message' => 'SMTP configuration has reached daily/hourly limits'
                ];
            }

            // Generate tracking ID
            $trackingId = Str::uuid();

            // Prepare email content with personalization
            $variables = [
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'full_name' => $contact->full_name,
                'email' => $contact->email,
                'company' => $contact->company,
                'phone' => $contact->phone,
            ];

            $personalizedSubject = $this->replaceVariables($subject, $variables);
            $personalizedContent = $this->replaceVariables($content, $variables);

            // Configure mailer with SMTP config
            config([
                'mail.mailers.smtp' => [
                    'transport' => 'smtp',
                    'host' => $smtpConfig->host,
                    'port' => $smtpConfig->port,
                    'encryption' => $smtpConfig->encryption,
                    'username' => $smtpConfig->username,
                    'password' => $smtpConfig->password,
                    'timeout' => null,
                ],
                'mail.from' => [
                    'address' => $smtpConfig->from_email,
                    'name' => $smtpConfig->from_name,
                ]
            ]);

            // Send email using Laravel Mail
            Mail::send([], [], function (Message $message) use ($personalizedSubject, $personalizedContent, $contact, $smtpConfig) {
                $message->to($contact->email, $contact->full_name)
                        ->subject($personalizedSubject)
                        ->from($smtpConfig->from_email, $smtpConfig->from_name)
                        ->html($personalizedContent);
            });

            // Create temporary campaign for logging
            $campaign = EmailCampaign::create([
                'name' => 'Quick Email: '.$personalizedSubject,
                'subject' => $personalizedSubject,
                'content' => $personalizedContent,
                'smtp_config_id' => $smtpConfig->id,
                'status' => 'sent',
                'total_recipients' => 1,
                'sent_count' => 1,
                'delivered_count' => 1,
                'sent_at' => now(),
                'created_by' => auth()->id(),
            ]);

            // Log email
            EmailLog::create([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'smtp_config_id' => $smtpConfig->id,
                'subject' => $personalizedSubject,
                'content' => $personalizedContent,
                'to_email' => $contact->email,
                'status' => 'sent',
                'sent_at' => now(),
                'tracking_id' => $trackingId,
            ]);

            // Update SMTP config stats
            $smtpConfig->incrementSent();

            // Create communication record
            $this->createCommunication($contact, $personalizedSubject, $personalizedContent);

            return [
                'success' => true,
                'message' => 'Email sent successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Quick Email Send Error: ' . $e->getMessage(), [
                'contact_id' => $contact->id,
                'contact_email' => $contact->email,
                'smtp_config_id' => $smtpConfig->id,
                'subject' => $subject,
                'error_trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ];
        }
    }

    // Add other methods that were in the original file...
    public function trackEmailOpen(string $trackingId, ?string $userAgent = null, ?string $ipAddress = null)
    {
        $emailLog = EmailLog::where('tracking_id', $trackingId)->first();

        if ($emailLog && ! $emailLog->opened_at) {
            $emailLog->markAsOpened($userAgent, $ipAddress);

            // Update campaign contact pivot
            if ($emailLog->campaign_id) {
                $emailLog->campaign->contacts()->updateExistingPivot($emailLog->contact_id, [
                    'status' => 'opened',
                    'opened_at' => now(),
                ]);
            }
        }

        // Return 1x1 transparent pixel
        return response()->make(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function trackEmailClick(string $trackingId, string $url, ?string $userAgent = null, ?string $ipAddress = null)
    {
        $emailLog = EmailLog::where('tracking_id', $trackingId)->first();

        if ($emailLog) {
            $emailLog->markAsClicked($userAgent, $ipAddress);

            // Update campaign contact pivot
            if ($emailLog->campaign_id) {
                $emailLog->campaign->contacts()->updateExistingPivot($emailLog->contact_id, [
                    'status' => 'clicked',
                    'clicked_at' => now(),
                ]);
            }
        }

        return redirect($url);
    }

    public function unsubscribe(string $trackingId)
    {
        $emailLog = EmailLog::where('tracking_id', $trackingId)->first();

        if ($emailLog && $emailLog->contact) {
            // Add unsubscribed tag to contact
            $emailLog->contact->addTag('unsubscribed');
            $emailLog->contact->update(['status' => 'inactive']);

            return [
                'success' => true,
                'message' => 'Successfully unsubscribed',
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid unsubscribe link',
        ];
    }
}
