<?php

namespace App\Services;

use App\Models\EmailCampaign;
use App\Models\EmailLog;
use App\Models\SmtpConfig;
use App\Models\Contact;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

class EmailService
{
    public function createCampaign(array $data)
    {
        $campaign = EmailCampaign::create([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'content' => $data['content'],
            'template_id' => $data['template_id'] ?? null,
            'smtp_config_id' => $data['smtp_config_id'],
            'status' => 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'settings' => $data['settings'] ?? [],
            'created_by' => auth()->id()
        ]);

        return $campaign;
    }

    public function addContactsToCampaign(EmailCampaign $campaign, array $contactIds)
    {
        $existingContacts = $campaign->contacts()->pluck('contact_id')->toArray();
        $newContactIds = array_diff($contactIds, $existingContacts);

        if (!empty($newContactIds)) {
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
            'total_recipients' => $campaign->contacts()->count()
        ];
    }

    public function sendCampaign(EmailCampaign $campaign)
    {
        if ($campaign->status !== 'draft' && $campaign->status !== 'scheduled') {
            return [
                'success' => false,
                'message' => 'Campaign cannot be sent from current status: ' . $campaign->status
            ];
        }

        // Check if scheduled time has arrived
        if ($campaign->scheduled_at && $campaign->scheduled_at->isFuture()) {
            return [
                'success' => false,
                'message' => 'Campaign is scheduled for future delivery'
            ];
        }

        $campaign->update(['status' => 'sending']);

        try {
            $smtpConfig = $campaign->smtpConfig;
            
            if (!$smtpConfig || !$smtpConfig->is_active) {
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
                    'message' => 'No pending recipients found'
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
                            'sent_at' => now()
                        ]);
                    } else {
                        $failed++;
                        $campaign->contacts()->updateExistingPivot($contact->id, [
                            'status' => 'failed',
                            'error_message' => $result['message']
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
                    Log::error('Email send error for contact ' . $contact->id . ': ' . $e->getMessage());
                    
                    $campaign->contacts()->updateExistingPivot($contact->id, [
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
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
                'sent_at' => now()
            ]);

            return [
                'success' => true,
                'message' => "Campaign sent. Success: {$sent}, Failed: {$failed}",
                'sent' => $sent,
                'failed' => $failed
            ];

        } catch (\Exception $e) {
            $campaign->update(['status' => 'failed']);
            
            Log::error('Campaign send error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Campaign send failed: ' . $e->getMessage()
            ];
        }
    }

    protected function sendEmailToContact(EmailCampaign $campaign, Contact $contact, SmtpConfig $smtpConfig)
    {
        try {
            if (!$smtpConfig->canSend()) {
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
                'phone' => $contact->phone
            ];

            $subject = $this->replaceVariables($campaign->subject, $variables);
            $content = $this->replaceVariables($campaign->content, $variables);

            // Add tracking pixels and unsubscribe links
            $content = $this->addTrackingElements($content, $trackingId);

            // Create SMTP transport
            $transport = Swift_SmtpTransport::newInstance($smtpConfig->host, $smtpConfig->port, $smtpConfig->encryption)
                ->setUsername($smtpConfig->username)
                ->setPassword(decrypt($smtpConfig->password));

            $mailer = Swift_Mailer::newInstance($transport);

            // Create message
            $message = Swift_Message::newInstance($subject)
                ->setFrom([$smtpConfig->from_address => $smtpConfig->from_name])
                ->setTo([$contact->email => $contact->full_name])
                ->setBody($content, 'text/html');

            // Add text version
            $textContent = strip_tags($content);
            $message->addPart($textContent, 'text/plain');

            // Send email
            $result = $mailer->send($message);

            if ($result) {
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
                    'tracking_id' => $trackingId
                ]);

                // Update SMTP config stats
                $smtpConfig->incrementSent();

                // Create communication record
                $this->createCommunication($contact, $subject, $content);

                return ['success' => true];
            }

            throw new \Exception('Failed to send email');

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
                'tracking_id' => $trackingId ?? null
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function replaceVariables(string $content, array $variables)
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value ?? '', $content);
        }

        return $content;
    }

    protected function addTrackingElements(string $content, string $trackingId)
    {
        $trackingPixel = '<img src="' . route('email.track.open', $trackingId) . '" width="1" height="1" style="display:none;" />';
        $unsubscribeLink = '<p style="font-size: 12px; color: #666; text-align: center; margin-top: 20px;">'
            . '<a href="' . route('email.unsubscribe', $trackingId) . '" style="color: #666;">Unsubscribe</a>'
            . '</p>';

        // Add tracking pixel before closing body tag
        if (strpos($content, '</body>') !== false) {
            $content = str_replace('</body>', $trackingPixel . '</body>', $content);
        } else {
            $content .= $trackingPixel;
        }

        // Add unsubscribe link
        if (strpos($content, '</body>') !== false) {
            $content = str_replace('</body>', $unsubscribeLink . '</body>', $content);
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
            'sent_at' => now()
        ]);
    }

    public function trackEmailOpen(string $trackingId, string $userAgent = null, string $ipAddress = null)
    {
        $emailLog = EmailLog::where('tracking_id', $trackingId)->first();

        if ($emailLog && !$emailLog->opened_at) {
            $emailLog->markAsOpened($userAgent, $ipAddress);

            // Update campaign contact pivot
            if ($emailLog->campaign_id) {
                $emailLog->campaign->contacts()->updateExistingPivot($emailLog->contact_id, [
                    'status' => 'opened',
                    'opened_at' => now()
                ]);
            }
        }

        // Return 1x1 transparent pixel
        return response()->make(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    public function trackEmailClick(string $trackingId, string $url, string $userAgent = null, string $ipAddress = null)
    {
        $emailLog = EmailLog::where('tracking_id', $trackingId)->first();

        if ($emailLog) {
            $emailLog->markAsClicked($userAgent, $ipAddress);

            // Update campaign contact pivot
            if ($emailLog->campaign_id) {
                $emailLog->campaign->contacts()->updateExistingPivot($emailLog->contact_id, [
                    'status' => 'clicked',
                    'clicked_at' => now()
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
                'message' => 'Successfully unsubscribed'
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid unsubscribe link'
        ];
    }

    public function sendSingleEmail(Contact $contact, string $subject, string $content, SmtpConfig $smtpConfig, EmailTemplate $template = null)
    {
        try {
            // Create a temporary campaign for single emails
            $campaign = EmailCampaign::create([
                'name' => 'Single Email: ' . $subject,
                'subject' => $subject,
                'content' => $content,
                'template_id' => $template?->id,
                'smtp_config_id' => $smtpConfig->id,
                'status' => 'sending',
                'total_recipients' => 1,
                'created_by' => auth()->id()
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
                    'delivered_count' => 1
                ]);
            } else {
                $campaign->update([
                    'status' => 'failed',
                    'failed_count' => 1
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Single email send error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ];
        }
    }

    public function bulkSend(array $contacts, string $subject, string $content, SmtpConfig $smtpConfig, EmailTemplate $template = null)
    {
        // Create campaign for bulk send
        $campaign = EmailCampaign::create([
            'name' => 'Bulk Email: ' . $subject,
            'subject' => $subject,
            'content' => $content,
            'template_id' => $template?->id,
            'smtp_config_id' => $smtpConfig->id,
            'status' => 'sending',
            'total_recipients' => count($contacts),
            'created_by' => auth()->id()
        ]);

        // Add contacts to campaign
        $contactIds = collect($contacts)->pluck('id')->toArray();
        $this->addContactsToCampaign($campaign, $contactIds);

        // Send campaign
        return $this->sendCampaign($campaign);
    }

    public function pauseCampaign(EmailCampaign $campaign)
    {
        if ($campaign->status !== 'sending') {
            return [
                'success' => false,
                'message' => 'Campaign cannot be paused from current status: ' . $campaign->status
            ];
        }

        $campaign->update(['status' => 'paused']);

        return [
            'success' => true,
            'message' => 'Campaign paused successfully'
        ];
    }

    public function resumeCampaign(EmailCampaign $campaign)
    {
        if ($campaign->status !== 'paused') {
            return [
                'success' => false,
                'message' => 'Campaign cannot be resumed from current status: ' . $campaign->status
            ];
        }

        $campaign->update(['status' => 'sending']);

        // Continue sending
        return $this->sendCampaign($campaign);
    }

    public function cancelCampaign(EmailCampaign $campaign)
    {
        if (!in_array($campaign->status, ['draft', 'scheduled', 'paused'])) {
            return [
                'success' => false,
                'message' => 'Campaign cannot be cancelled from current status: ' . $campaign->status
            ];
        }

        $campaign->update(['status' => 'cancelled']);

        return [
            'success' => true,
            'message' => 'Campaign cancelled successfully'
        ];
    }

    public function getCampaignStats(EmailCampaign $campaign)
    {
        return [
            'total_recipients' => $campaign->total_recipients,
            'sent_count' => $campaign->sent_count,
            'delivered_count' => $campaign->delivered_count,
            'opened_count' => $campaign->opened_count,
            'clicked_count' => $campaign->clicked_count,
            'bounced_count' => $campaign->bounced_count,
            'failed_count' => $campaign->failed_count,
            'open_rate' => $campaign->open_rate,
            'click_rate' => $campaign->click_rate,
            'bounce_rate' => $campaign->bounce_rate
        ];
    }

    public function getHourlyStats(EmailCampaign $campaign)
    {
        $logs = $campaign->logs()->whereNotNull('opened_at')
            ->selectRaw('HOUR(opened_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyData[$i] = 0;
        }

        foreach ($logs as $log) {
            $hourlyData[$log->hour] = $log->count;
        }

        return $hourlyData;
    }

    public function getClickStats(EmailCampaign $campaign)
    {
        return $campaign->logs()
            ->whereNotNull('clicked_at')
            ->selectRaw('DATE(clicked_at) as date, COUNT(*) as clicks')
            ->groupBy('date')
            ->orderBy('date')
            ->limit(30)
            ->get();
    }

    public function getDeviceStats(EmailCampaign $campaign)
    {
        $stats = $campaign->logs()
            ->whereNotNull('user_agent')
            ->selectRaw('user_agent, COUNT(*) as count')
            ->groupBy('user_agent')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $deviceStats = [
            'desktop' => 0,
            'mobile' => 0,
            'tablet' => 0,
            'unknown' => 0
        ];

        foreach ($stats as $stat) {
            $userAgent = strtolower($stat->user_agent);
            if (strpos($userAgent, 'mobile') !== false || strpos($userAgent, 'android') !== false || strpos($userAgent, 'iphone') !== false) {
                $deviceStats['mobile'] += $stat->count;
            } elseif (strpos($userAgent, 'tablet') !== false || strpos($userAgent, 'ipad') !== false) {
                $deviceStats['tablet'] += $stat->count;
            } elseif (strpos($userAgent, 'mozilla') !== false || strpos($userAgent, 'chrome') !== false || strpos($userAgent, 'safari') !== false) {
                $deviceStats['desktop'] += $stat->count;
            } else {
                $deviceStats['unknown'] += $stat->count;
            }
        }

        return $deviceStats;
    }

    public function duplicateCampaign(EmailCampaign $campaign)
    {
        $duplicated = EmailCampaign::create([
            'name' => $campaign->name . ' - Copy',
            'subject' => $campaign->subject,
            'content' => $campaign->content,
            'template_id' => $campaign->template_id,
            'smtp_config_id' => $campaign->smtp_config_id,
            'status' => 'draft',
            'scheduled_at' => null,
            'settings' => $campaign->settings,
            'created_by' => auth()->id()
        ]);

        // Copy contacts
        $contactIds = $campaign->contacts()->pluck('contact_id')->toArray();
        if (!empty($contactIds)) {
            $this->addContactsToCampaign($duplicated, $contactIds);
        }

        return $duplicated;
    }

    public function generatePreview(EmailCampaign $campaign, Contact $contact)
    {
        $variables = [
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'full_name' => $contact->full_name,
            'email' => $contact->email,
            'company' => $contact->company,
            'phone' => $contact->phone
        ];

        $subject = $this->replaceVariables($campaign->subject, $variables);
        $content = $this->replaceVariables($campaign->content, $variables);

        // Remove tracking elements for preview
        $content = preg_replace('/<img[^>]+src="[^"]*track[^"]*"[^>]*>/i', '', $content);
        $content = preg_replace('/<p[^>]*>.*?<a[^>]+href="[^"]*unsubscribe[^"]*"[^>]*>.*?<\/p>/i', '', $content);

        return [
            'subject' => $subject,
            'content' => $content
        ];
    }
}
