<?php

namespace App\Jobs;

use App\Models\GoogleAccount;
use App\Services\GmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class GmailSendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 60;

    /**
     * Delete the job if its models no longer exist.
     */
    public $deleteWhenMissingModels = true;

    private GoogleAccount $account;
    private array $emailData;
    private ?string $campaignId;

    /**
     * Create a new job instance.
     */
    public function __construct(GoogleAccount $account, array $emailData, ?string $campaignId = null)
    {
        $this->account = $account;
        $this->emailData = $emailData;
        $this->campaignId = $campaignId;
    }

    /**
     * Execute the job.
     */
    public function handle(GmailService $gmailService): void
    {
        try {
            Log::info('Gmail send mail job started', [
                'account_id' => $this->account->id,
                'to' => $this->emailData['to'] ?? 'N/A',
                'subject' => $this->emailData['subject'] ?? 'N/A',
                'campaign_id' => $this->campaignId
            ]);

            // Check if account is active and has valid tokens
            if ($this->account->status !== 'active') {
                throw new Exception("Gmail account is not active (status: {$this->account->status})");
            }

            // Send the email
            $result = $gmailService->sendEmail($this->account, $this->emailData);

            if ($result['success']) {
                Log::info('Gmail send mail job completed successfully', [
                    'account_id' => $this->account->id,
                    'gmail_id' => $result['gmail_id'] ?? null,
                    'to' => $this->emailData['to'] ?? 'N/A',
                    'campaign_id' => $this->campaignId
                ]);

                // Log the sent email if it's part of a campaign
                if ($this->campaignId) {
                    $this->logCampaignEmail($result['gmail_id'] ?? null);
                }

                // Update SMTP statistics for the Gmail account
                $this->updateSendStatistics(true);

            } else {
                Log::error('Gmail send mail job failed', [
                    'account_id' => $this->account->id,
                    'message' => $result['message'] ?? 'Unknown error',
                    'to' => $this->emailData['to'] ?? 'N/A',
                    'campaign_id' => $this->campaignId
                ]);

                $this->updateSendStatistics(false);

                throw new Exception($result['message'] ?? 'Failed to send email');
            }

        } catch (Exception $e) {
            Log::error('Gmail send mail job exception', [
                'account_id' => $this->account->id,
                'to' => $this->emailData['to'] ?? 'N/A',
                'error' => $e->getMessage(),
                'campaign_id' => $this->campaignId
            ]);

            $this->updateSendStatistics(false);

            throw $e;
        }
    }

    /**
     * Log campaign email send.
     */
    private function logCampaignEmail(?string $gmailId): void
    {
        try {
            // If this is part of a campaign, create email log entry
            if ($this->campaignId) {
                \App\Models\EmailLog::create([
                    'campaign_id' => $this->campaignId,
                    'recipient_email' => $this->emailData['to'],
                    'recipient_name' => $this->emailData['recipient_name'] ?? null,
                    'subject' => $this->emailData['subject'] ?? null,
                    'status' => 'sent',
                    'sent_at' => now(),
                    'gmail_message_id' => $gmailId,
                    'smtp_config_id' => null, // This is Gmail, not SMTP
                    'google_account_id' => $this->account->id,
                ]);
            }
        } catch (Exception $e) {
            Log::warning('Failed to log campaign email', [
                'campaign_id' => $this->campaignId,
                'gmail_id' => $gmailId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update send statistics for the Google account.
     */
    private function updateSendStatistics(bool $success): void
    {
        try {
            // Create a simple counter or use existing statistics system
            $key = $success ? 'emails_sent' : 'emails_failed';
            
            // You could implement this similar to SmtpConfig statistics
            // For now, we'll just log it
            Log::debug('Gmail send statistics updated', [
                'account_id' => $this->account->id,
                'status' => $success ? 'success' : 'failed',
                'timestamp' => now()
            ]);

        } catch (Exception $e) {
            Log::warning('Failed to update send statistics', [
                'account_id' => $this->account->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Gmail send mail job failed permanently', [
            'account_id' => $this->account->id,
            'to' => $this->emailData['to'] ?? 'N/A',
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
            'campaign_id' => $this->campaignId
        ]);

        // If this is part of a campaign, log the failure
        if ($this->campaignId) {
            try {
                \App\Models\EmailLog::create([
                    'campaign_id' => $this->campaignId,
                    'recipient_email' => $this->emailData['to'],
                    'recipient_name' => $this->emailData['recipient_name'] ?? null,
                    'subject' => $this->emailData['subject'] ?? null,
                    'status' => 'failed',
                    'sent_at' => now(),
                    'error_message' => $exception->getMessage(),
                    'google_account_id' => $this->account->id,
                ]);
            } catch (Exception $e) {
                Log::error('Failed to log campaign email failure', [
                    'campaign_id' => $this->campaignId,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        $tags = [
            'gmail-send',
            'account:' . $this->account->id,
            'user:' . $this->account->user_id
        ];

        if ($this->campaignId) {
            $tags[] = 'campaign:' . $this->campaignId;
        }

        return $tags;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [10, 30, 60]; // 10 seconds, 30 seconds, 1 minute
    }

    /**
     * Determine if the job should be retried based on the exception.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10); // Stop retrying after 10 minutes
    }
}
