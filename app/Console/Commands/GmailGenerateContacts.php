<?php

namespace App\Console\Commands;

use App\Models\Contact;
use App\Models\Email;
use App\Models\GoogleAccount;
use App\Services\GmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GmailGenerateContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmail:generate-contacts 
                            {--account-id= : Specific Google Account ID to process} 
                            {--force : Overwrite existing contacts}
                            {--limit=100 : Limit number of emails to process}
                            {--dry-run : Show what would be created without actually creating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate contacts from existing Gmail messages';

    private GmailService $gmailService;

    /**
     * Create a new command instance.
     */
    public function __construct(GmailService $gmailService)
    {
        parent::__construct();
        $this->gmailService = $gmailService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Gmail Contact Generation...');

        $accountId = $this->option('account-id');
        $force = $this->option('force');
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        // Get Google accounts to process
        $accounts = $accountId ? 
            GoogleAccount::where('id', $accountId)->get() : 
            GoogleAccount::where('status', 'active')->get();

        if ($accounts->isEmpty()) {
            $this->error('❌ No active Google accounts found!');
            return 1;
        }

        $this->info("📧 Processing {$accounts->count()} Google account(s)");

        $totalProcessed = 0;
        $totalCreated = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;

        foreach ($accounts as $account) {
            $this->info("\n📁 Processing account: {$account->email} (ID: {$account->id})");
            
            // Get emails for this account
            $emails = Email::where('google_account_id', $account->id)
                ->whereNotNull('from_email')
                ->when($limit > 0, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('date_received', 'desc')
                ->get();

            if ($emails->isEmpty()) {
                $this->warn("⚠️  No emails found for account {$account->email}");
                continue;
            }

            $this->info("📨 Found {$emails->count()} emails to process");

            $progressBar = $this->output->createProgressBar($emails->count());
            $progressBar->start();

            foreach ($emails as $email) {
                $result = $this->processEmailForContacts($email, $account, $force, $dryRun);
                
                $totalProcessed++;
                $totalCreated += $result['created'];
                $totalUpdated += $result['updated'];
                $totalSkipped += $result['skipped'];

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();
        }

        // Final summary
        $this->newLine();
        $this->info("✅ Contact Generation Complete!");
        $this->table(['Metric', 'Count'], [
            ['Emails Processed', $totalProcessed],
            ['Contacts Created', $totalCreated],
            ['Contacts Updated', $totalUpdated],
            ['Contacts Skipped', $totalSkipped],
        ]);

        if ($dryRun) {
            $this->info("🔍 This was a dry run - no actual changes were made.");
            $this->info("💡 Run without --dry-run to create/update contacts.");
        }

        return 0;
    }

    /**
     * Process a single email for contact generation.
     */
    private function processEmailForContacts(Email $email, GoogleAccount $account, bool $force, bool $dryRun): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;

        // Collect all email addresses from the message
        $emailAddresses = array_merge(
            [$email->from_email],
            $email->to_recipients ?? [],
            $email->cc_recipients ?? [],
            $email->bcc_recipients ?? []
        );

        // Remove duplicates and filter out the account owner's email
        $emailAddresses = array_unique(array_filter($emailAddresses, function ($addr) use ($account) {
            return !empty($addr) && $addr !== $account->email;
        }));

        foreach ($emailAddresses as $emailAddress) {
            // Check if contact already exists
            $existingContact = Contact::where('email', $emailAddress)
                ->where('user_id', $account->user_id)
                ->first();

            if ($existingContact) {
                if ($force) {
                    if (!$dryRun) {
                        $this->updateExistingContact($existingContact, $email);
                    }
                    $updated++;
                } else {
                    $skipped++;
                }
                continue;
            }

            // Extract name and create new contact
            if (!$dryRun) {
                $this->createNewContact($emailAddress, $email, $account);
            }
            $created++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /**
     * Create a new contact from email data.
     */
    private function createNewContact(string $emailAddress, Email $email, GoogleAccount $account): void
    {
        // Extract name from email context
        $name = $this->extractNameFromEmail($emailAddress, $email);
        $nameParts = $this->splitName($name);

        // Extract additional info from email signature if possible
        $additionalInfo = $this->extractInfoFromSignature($email->body_text ?? $email->body_html);

        Contact::create([
            'user_id' => $account->user_id,
            'first_name' => $nameParts['first_name'],
            'last_name' => $nameParts['last_name'],
            'email' => $emailAddress,
            'company' => $additionalInfo['company'] ?? null,
            'phone' => $additionalInfo['phone'] ?? null,
            'source' => 'gmail_auto',
            'source_metadata' => [
                'gmail_account_id' => $account->id,
                'created_from_email_id' => $email->id,
                'email_subject' => $email->subject,
                'email_date' => $email->date_received->toDateString(),
                'thread_id' => $email->thread_id,
            ],
            'tags' => ['gmail-auto-created'],
            'notes' => "Auto-created from Gmail message: \"{$email->subject}\" on {$email->date_received->format('Y-m-d H:i')}",
            'custom_fields' => [
                'gmail_thread_id' => $email->thread_id,
                'original_email_id' => $email->id,
                'extraction_method' => 'gmail_command',
            ],
            'first_email_at' => $email->date_received,
            'last_email_at' => $email->date_received,
            'email_count' => 1,
            'created_by' => $account->user_id,
        ]);
    }

    /**
     * Update existing contact with email data.
     */
    private function updateExistingContact(Contact $contact, Email $email): void
    {
        $updates = [];

        // Update email statistics
        if (!$contact->first_email_at || $email->date_received < $contact->first_email_at) {
            $updates['first_email_at'] = $email->date_received;
        }

        if (!$contact->last_email_at || $email->date_received > $contact->last_email_at) {
            $updates['last_email_at'] = $email->date_received;
        }

        $updates['email_count'] = DB::raw('email_count + 1');

        // Add Gmail tag if not already present
        $tags = $contact->tags ?? [];
        if (!in_array('gmail-auto-created', $tags)) {
            $tags[] = 'gmail-auto-created';
            $updates['tags'] = $tags;
        }

        // Update source metadata
        $sourceMetadata = $contact->source_metadata ?? [];
        $sourceMetadata['last_gmail_update'] = now()->toDateString();
        $sourceMetadata['latest_email_id'] = $email->id;
        $updates['source_metadata'] = $sourceMetadata;

        if (!empty($updates)) {
            $contact->update($updates);
        }
    }

    /**
     * Extract name from email address based on email context.
     */
    private function extractNameFromEmail(string $emailAddress, Email $email): string
    {
        // If this is the from address, use from_name
        if ($emailAddress === $email->from_email && $email->from_name) {
            return $email->from_name;
        }

        // Try to find name in email headers or body
        $headers = $email->headers ?? [];
        foreach ($headers as $key => $value) {
            if (strpos(strtolower($value), strtolower($emailAddress)) !== false) {
                // Try to extract name from header value
                if (preg_match('/^"?([^"<]*)"?\s*<' . preg_quote($emailAddress, '/') . '>/', $value, $matches)) {
                    return trim($matches[1]);
                }
            }
        }

        // Fallback: extract from the username part of email
        $localPart = explode('@', $emailAddress)[0];
        $name = str_replace(['.', '_', '-', '+'], ' ', $localPart);
        return ucwords($name);
    }

    /**
     * Split full name into first and last name.
     */
    private function splitName(string $fullName): array
    {
        $nameParts = array_filter(explode(' ', trim($fullName)));
        
        if (count($nameParts) === 0) {
            return ['first_name' => '', 'last_name' => ''];
        }

        if (count($nameParts) === 1) {
            return [
                'first_name' => $nameParts[0],
                'last_name' => ''
            ];
        }

        return [
            'first_name' => $nameParts[0],
            'last_name' => implode(' ', array_slice($nameParts, 1))
        ];
    }

    /**
     * Extract additional information from email signature.
     */
    private function extractInfoFromSignature(string $content): array
    {
        $info = [];

        if (empty($content)) {
            return $info;
        }

        // Extract phone numbers
        if (preg_match('/(?:phone?|tel|mobile?|cell)[:\s]*([+]?[\d\s\-\(\)\.]{10,})/i', $content, $matches)) {
            $info['phone'] = preg_replace('/[^\d+]/', '', $matches[1]);
        }

        // Extract company names (lines that might be company names)
        $lines = explode("\n", strip_tags($content));
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) > 3 && strlen($line) < 50 && 
                !filter_var($line, FILTER_VALIDATE_EMAIL) &&
                !preg_match('/^\d+/', $line) &&
                !preg_match('/^(best|regards|thanks|sincerely)/i', $line)) {
                $info['company'] = $line;
                break;
            }
        }

        return $info;
    }
}
