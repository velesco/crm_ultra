<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Email;
use App\Models\GoogleAccount;
use App\Services\GmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ContactEnrichmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Contact $contact;
    private ?GoogleAccount $googleAccount;

    /**
     * Create a new job instance.
     */
    public function __construct(Contact $contact, ?GoogleAccount $googleAccount = null)
    {
        $this->contact = $contact;
        $this->googleAccount = $googleAccount;
    }

    /**
     * Execute the job.
     */
    public function handle(GmailService $gmailService): void
    {
        try {
            Log::info('Starting contact enrichment', [
                'contact_id' => $this->contact->id,
                'contact_email' => $this->contact->email,
                'google_account_id' => $this->googleAccount?->id
            ]);

            // Enrich contact data from Gmail emails
            $this->enrichFromGmailEmails();

            // Extract company info from email signatures
            $this->enrichFromEmailSignatures();

            // Extract social profiles from email signatures
            $this->enrichSocialProfiles();

            // Update contact interaction statistics
            $this->updateInteractionStatistics();

            Log::info('Contact enrichment completed successfully', [
                'contact_id' => $this->contact->id
            ]);

        } catch (Exception $e) {
            Log::error('Contact enrichment failed', [
                'contact_id' => $this->contact->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to trigger job failure handling
            throw $e;
        }
    }

    /**
     * Enrich contact data from Gmail emails.
     */
    private function enrichFromGmailEmails(): void
    {
        if (!$this->googleAccount) {
            return;
        }

        // Find all emails involving this contact
        $emails = Email::where('google_account_id', $this->googleAccount->id)
            ->where(function ($query) {
                $query->where('from_email', $this->contact->email)
                      ->orWhereJsonContains('to_recipients', $this->contact->email)
                      ->orWhereJsonContains('cc_recipients', $this->contact->email);
            })
            ->orderBy('date_received', 'desc')
            ->limit(20) // Process latest 20 emails
            ->get();

        if ($emails->isEmpty()) {
            return;
        }

        $enrichmentData = [
            'companies' => [],
            'phones' => [],
            'addresses' => [],
            'social_profiles' => [],
            'interaction_data' => []
        ];

        foreach ($emails as $email) {
            // Extract company information
            $company = $this->extractCompanyFromEmail($email);
            if ($company && !in_array($company, $enrichmentData['companies'])) {
                $enrichmentData['companies'][] = $company;
            }

            // Extract phone numbers
            $phone = $this->extractPhoneFromEmail($email);
            if ($phone && !in_array($phone, $enrichmentData['phones'])) {
                $enrichmentData['phones'][] = $phone;
            }

            // Extract addresses
            $address = $this->extractAddressFromEmail($email);
            if ($address) {
                $enrichmentData['addresses'][] = $address;
            }

            // Extract social profiles
            $social = $this->extractSocialProfilesFromEmail($email);
            $enrichmentData['social_profiles'] = array_merge($enrichmentData['social_profiles'], $social);

            // Collect interaction data
            $enrichmentData['interaction_data'][] = [
                'date' => $email->date_received,
                'subject' => $email->subject,
                'type' => $email->from_email === $this->contact->email ? 'sent' : 'received',
                'thread_id' => $email->thread_id
            ];
        }

        // Update contact with enriched data
        $this->updateContactWithEnrichedData($enrichmentData);
    }

    /**
     * Extract company information from email.
     */
    private function extractCompanyFromEmail(Email $email): ?string
    {
        $content = $email->body_text ?? strip_tags($email->body_html ?? '');
        
        // Look for company name in email signature
        $lines = explode("\n", $content);
        $signatureLines = array_slice($lines, -10); // Last 10 lines likely signature
        
        foreach ($signatureLines as $line) {
            $line = trim($line);
            
            // Skip empty lines, email addresses, phone numbers, URLs
            if (empty($line) || 
                filter_var($line, FILTER_VALIDATE_EMAIL) ||
                preg_match('/^\+?[\d\s\-\(\)\.]+$/', $line) ||
                preg_match('/^https?:\/\//', $line) ||
                strlen($line) < 3 ||
                strlen($line) > 100) {
                continue;
            }
            
            // Skip common signature phrases
            if (preg_match('/^(best regards|thanks|sincerely|cheers|kind regards)/i', $line)) {
                continue;
            }
            
            // This might be a company name
            if (!preg_match('/^\d/', $line) && preg_match('/[a-zA-Z]/', $line)) {
                return $line;
            }
        }
        
        return null;
    }

    /**
     * Extract phone number from email.
     */
    private function extractPhoneFromEmail(Email $email): ?string
    {
        $content = $email->body_text ?? strip_tags($email->body_html ?? '');
        
        // Look for phone patterns
        $patterns = [
            '/(?:phone?|tel|mobile?|cell)[:\s]*([+]?[\d\s\-\(\)\.]{10,})/i',
            '/([+]?[\d\s\-\(\)\.]{10,})/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $phone = preg_replace('/[^\d+]/', '', $matches[1]);
                if (strlen($phone) >= 10) {
                    return $phone;
                }
            }
        }
        
        return null;
    }

    /**
     * Extract address from email.
     */
    private function extractAddressFromEmail(Email $email): ?array
    {
        $content = $email->body_text ?? strip_tags($email->body_html ?? '');
        
        // Look for address patterns (very basic)
        if (preg_match('/(\d+[\w\s,.-]+(?:street|st|avenue|ave|road|rd|boulevard|blvd|lane|ln|drive|dr|way|place|pl)[\w\s,.-]*\d{5})/i', $content, $matches)) {
            return [
                'full_address' => trim($matches[1]),
                'extracted_from' => 'email_signature'
            ];
        }
        
        return null;
    }

    /**
     * Extract social profiles from email.
     */
    private function extractSocialProfilesFromEmail(Email $email): array
    {
        $content = $email->body_text ?? strip_tags($email->body_html ?? '');
        $profiles = [];
        
        // LinkedIn
        if (preg_match('/linkedin\.com\/in\/([\w\-]+)/i', $content, $matches)) {
            $profiles['linkedin_url'] = 'https://linkedin.com/in/' . $matches[1];
        }
        
        // Twitter
        if (preg_match('/twitter\.com\/([\w\-]+)/i', $content, $matches)) {
            $profiles['twitter_handle'] = '@' . $matches[1];
        }
        
        // Instagram
        if (preg_match('/instagram\.com\/([\w\-\.]+)/i', $content, $matches)) {
            $profiles['instagram_handle'] = '@' . $matches[1];
        }
        
        return $profiles;
    }

    /**
     * Enrich from email signatures.
     */
    private function enrichFromEmailSignatures(): void
    {
        // Get emails where this contact was the sender (their signature)
        $senderEmails = Email::whereHas('googleAccount', function ($query) {
            if ($this->googleAccount) {
                $query->where('id', $this->googleAccount->id);
            }
        })
        ->where('from_email', $this->contact->email)
        ->orderBy('date_received', 'desc')
        ->limit(5)
        ->get();

        foreach ($senderEmails as $email) {
            $signature = $this->extractSignatureFromEmail($email);
            if ($signature) {
                $this->processSignatureData($signature);
            }
        }
    }

    /**
     * Extract signature from email.
     */
    private function extractSignatureFromEmail(Email $email): ?array
    {
        $content = $email->body_text ?? strip_tags($email->body_html ?? '');
        $lines = explode("\n", $content);
        
        // Look for signature separator or take last lines
        $signatureStart = count($lines);
        foreach ($lines as $index => $line) {
            if (preg_match('/^-{2,}|^_{2,}|^={2,}/', trim($line))) {
                $signatureStart = $index + 1;
                break;
            }
        }
        
        $signatureLines = array_slice($lines, max(0, $signatureStart), 10);
        $signatureLines = array_filter(array_map('trim', $signatureLines));
        
        if (empty($signatureLines)) {
            return null;
        }
        
        return [
            'lines' => $signatureLines,
            'full_signature' => implode("\n", $signatureLines)
        ];
    }

    /**
     * Process signature data for enrichment.
     */
    private function processSignatureData(array $signature): void
    {
        $updates = [];
        
        foreach ($signature['lines'] as $line) {
            // Check for job title indicators
            if (preg_match('/^(CEO|CTO|VP|Director|Manager|Senior|Lead|Head of|Chief)/i', $line)) {
                $updates['position'] = $line;
            }
            
            // Check for company name (not email, not phone, not URL)
            if (!filter_var($line, FILTER_VALIDATE_EMAIL) &&
                !preg_match('/^\+?[\d\s\-\(\)\.]+$/', $line) &&
                !preg_match('/^https?:\/\//', $line) &&
                strlen($line) > 3 && strlen($line) < 100 &&
                !isset($updates['company'])) {
                
                if (!preg_match('/^(best regards|thanks|sincerely|cheers|kind regards)/i', $line)) {
                    $updates['company'] = $line;
                }
            }
        }
        
        // Update contact if we found new information
        if (!empty($updates)) {
            $this->contact->update(array_filter($updates, function ($value) {
                return !empty($value);
            }));
        }
    }

    /**
     * Enrich social profiles.
     */
    private function enrichSocialProfiles(): void
    {
        $socialProfiles = $this->contact->social_profiles ?? [];
        $updated = false;

        // Try to find social profiles from various sources
        $newProfiles = $this->findSocialProfiles();
        
        foreach ($newProfiles as $platform => $profile) {
            if (!isset($socialProfiles[$platform])) {
                $socialProfiles[$platform] = $profile;
                $updated = true;
            }
        }

        if ($updated) {
            $this->contact->update(['social_profiles' => $socialProfiles]);
        }
    }

    /**
     * Find social profiles for the contact.
     */
    private function findSocialProfiles(): array
    {
        $profiles = [];
        
        // Basic email-to-social mapping (could be enhanced with external APIs)
        $emailUsername = explode('@', $this->contact->email)[0];
        
        // Generate potential social profile URLs (these would need verification in real implementation)
        if (strlen($emailUsername) > 3) {
            $profiles['potential_linkedin'] = "https://linkedin.com/in/{$emailUsername}";
            $profiles['potential_twitter'] = "https://twitter.com/{$emailUsername}";
        }
        
        return $profiles;
    }

    /**
     * Update interaction statistics.
     */
    private function updateInteractionStatistics(): void
    {
        if (!$this->googleAccount) {
            return;
        }

        $emailStats = Email::where('google_account_id', $this->googleAccount->id)
            ->where(function ($query) {
                $query->where('from_email', $this->contact->email)
                      ->orWhereJsonContains('to_recipients', $this->contact->email)
                      ->orWhereJsonContains('cc_recipients', $this->contact->email);
            })
            ->selectRaw('
                COUNT(*) as total_emails,
                MIN(date_received) as first_email,
                MAX(date_received) as last_email,
                SUM(CASE WHEN from_email = ? THEN 1 ELSE 0 END) as emails_received,
                SUM(CASE WHEN from_email != ? THEN 1 ELSE 0 END) as emails_sent
            ', [$this->contact->email, $this->contact->email])
            ->first();

        if ($emailStats) {
            $this->contact->update([
                'first_email_at' => $emailStats->first_email,
                'last_email_at' => $emailStats->last_email,
                'email_count' => $emailStats->total_emails,
                'source_metadata' => array_merge($this->contact->source_metadata ?? [], [
                    'emails_received' => $emailStats->emails_received,
                    'emails_sent' => $emailStats->emails_sent,
                    'last_enrichment' => now()->toDateString(),
                ])
            ]);
        }
    }

    /**
     * Update contact with enriched data.
     */
    private function updateContactWithEnrichedData(array $enrichmentData): void
    {
        $updates = [];

        // Update company if not set and we found companies
        if (empty($this->contact->company) && !empty($enrichmentData['companies'])) {
            $updates['company'] = $enrichmentData['companies'][0];
        }

        // Update phone if not set and we found phones
        if (empty($this->contact->phone) && !empty($enrichmentData['phones'])) {
            $updates['phone'] = $enrichmentData['phones'][0];
        }

        // Update address if not set and we found addresses
        if (empty($this->contact->address) && !empty($enrichmentData['addresses'])) {
            $address = $enrichmentData['addresses'][0];
            $updates['address'] = $address['full_address'];
        }

        // Update social profiles
        $currentProfiles = $this->contact->social_profiles ?? [];
        $newProfiles = $enrichmentData['social_profiles'];
        if (!empty($newProfiles)) {
            $updates['social_profiles'] = array_merge($currentProfiles, $newProfiles);
        }

        // Update custom fields with enrichment data
        $customFields = $this->contact->custom_fields ?? [];
        $customFields['gmail_enrichment'] = [
            'last_enriched' => now()->toISOString(),
            'emails_analyzed' => count($enrichmentData['interaction_data']),
            'companies_found' => $enrichmentData['companies'],
            'phones_found' => $enrichmentData['phones'],
            'enrichment_source' => 'gmail_job'
        ];
        $updates['custom_fields'] = $customFields;

        // Add enrichment tags
        $tags = $this->contact->tags ?? [];
        if (!in_array('gmail-enriched', $tags)) {
            $tags[] = 'gmail-enriched';
            $updates['tags'] = $tags;
        }

        // Apply updates
        if (!empty($updates)) {
            $this->contact->update($updates);
            
            Log::info('Contact enriched with data', [
                'contact_id' => $this->contact->id,
                'updates_applied' => array_keys($updates)
            ]);
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 120]; // Retry after 30s, 60s, 2min
    }
}
