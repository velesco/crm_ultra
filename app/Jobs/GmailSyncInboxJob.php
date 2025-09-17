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

class GmailSyncInboxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 300; // 5 minutes

    /**
     * Delete the job if its models no longer exist.
     */
    public $deleteWhenMissingModels = true;

    private GoogleAccount $account;
    private int $maxResults;

    /**
     * Create a new job instance.
     */
    public function __construct(GoogleAccount $account, int $maxResults = 100)
    {
        $this->account = $account;
        $this->maxResults = $maxResults;
    }

    /**
     * Execute the job.
     */
    public function handle(GmailService $gmailService): void
    {
        try {
            Log::info('Gmail sync job started', [
                'account_id' => $this->account->id,
                'email' => $this->account->email,
                'max_results' => $this->maxResults
            ]);

            // Check if account is still active and has valid tokens
            if ($this->account->status !== 'active') {
                Log::warning('Skipping sync for inactive account', [
                    'account_id' => $this->account->id,
                    'status' => $this->account->status
                ]);
                return;
            }

            // Perform the sync
            $result = $gmailService->syncMessages($this->account, $this->maxResults);

            if ($result['success']) {
                Log::info('Gmail sync job completed successfully', [
                    'account_id' => $this->account->id,
                    'processed' => $result['processed'] ?? 0,
                    'created' => $result['created'] ?? 0,
                    'updated' => $result['updated'] ?? 0,
                    'failed' => $result['failed'] ?? 0
                ]);

                // Schedule next sync if auto-sync is enabled
                if ($this->account->auto_sync_enabled && $this->account->needsSync()) {
                    $this->scheduleNextSync();
                }
            } else {
                Log::error('Gmail sync job failed', [
                    'account_id' => $this->account->id,
                    'message' => $result['message'] ?? 'Unknown error'
                ]);

                // Don't fail the job for API errors - just log them
                // The account status will be updated by the service
            }

        } catch (Exception $e) {
            Log::error('Gmail sync job exception', [
                'account_id' => $this->account->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to trigger job retry
            throw $e;
        }
    }

    /**
     * Schedule the next sync job.
     */
    private function scheduleNextSync(): void
    {
        try {
            $delay = now()->addMinutes($this->account->sync_frequency_minutes);

            static::dispatch($this->account, $this->maxResults)
                  ->delay($delay);

            Log::debug('Next Gmail sync scheduled', [
                'account_id' => $this->account->id,
                'scheduled_at' => $delay->toDateTimeString()
            ]);

        } catch (Exception $e) {
            Log::error('Failed to schedule next Gmail sync', [
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
        Log::error('Gmail sync job failed permanently', [
            'account_id' => $this->account->id,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);

        // Update account status if too many failures
        if ($this->attempts() >= $this->tries) {
            $this->account->update([
                'status' => 'sync_failed',
                'last_error' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'gmail-sync',
            'account:' . $this->account->id,
            'user:' . $this->account->user_id
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 120, 300]; // 30 seconds, 2 minutes, 5 minutes
    }
}
