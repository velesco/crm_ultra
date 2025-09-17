<?php

namespace App\Console\Commands;

use App\Models\GoogleAccount;
use App\Jobs\GmailSyncInboxJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GmailAutoSync extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gmail:auto-sync 
                            {--account= : Sync specific account by ID}
                            {--user= : Sync accounts for specific user}
                            {--max-results=50 : Maximum results per sync}
                            {--force : Force sync even if not needed}';

    /**
     * The console command description.
     */
    protected $description = 'Automatically sync Gmail accounts that need synchronization';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting Gmail auto-sync process...');

        try {
            // Get accounts that need syncing
            $accounts = $this->getAccountsForSync();

            if ($accounts->isEmpty()) {
                $this->info('✅ No Gmail accounts need syncing at this time.');
                return self::SUCCESS;
            }

            $this->info("📧 Found {$accounts->count()} Gmail account(s) that need syncing.");

            $syncedCount = 0;
            $errorCount = 0;

            foreach ($accounts as $account) {
                try {
                    $this->syncAccount($account);
                    $syncedCount++;
                    
                    $this->line("✅ Queued sync for: {$account->email} (ID: {$account->id})");
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("❌ Failed to queue sync for {$account->email}: {$e->getMessage()}");
                    
                    Log::error('Gmail auto-sync command error', [
                        'account_id' => $account->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->newLine();
            $this->info("📊 Sync Summary:");
            $this->line("   • Accounts queued: {$syncedCount}");
            $this->line("   • Errors: {$errorCount}");
            $this->line("   • Total processed: {$accounts->count()}");

            if ($errorCount === 0) {
                $this->info('🎉 All Gmail accounts successfully queued for sync!');
                return self::SUCCESS;
            } else {
                $this->warn("⚠️  Completed with {$errorCount} error(s). Check logs for details.");
                return self::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('💥 Gmail auto-sync failed: ' . $e->getMessage());
            
            Log::error('Gmail auto-sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return self::FAILURE;
        }
    }

    /**
     * Get accounts that need syncing based on command options.
     */
    private function getAccountsForSync()
    {
        $query = GoogleAccount::query()->active();

        // Filter by specific account
        if ($accountId = $this->option('account')) {
            $query->where('id', $accountId);
        }

        // Filter by specific user
        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
        }

        // If not forcing, only get accounts that actually need sync
        if (!$this->option('force')) {
            $query->where(function ($q) {
                $q->where('auto_sync_enabled', true)
                  ->where(function ($subQ) {
                      // First time sync (never synced)
                      $subQ->whereNull('last_sync_at')
                           // Or enough time has passed since last sync
                           ->orWhereRaw('
                               last_sync_at IS NOT NULL 
                               AND last_sync_at <= DATE_SUB(NOW(), INTERVAL sync_frequency_minutes MINUTE)
                           ');
                  });
            });
        }

        return $query->orderBy('last_sync_at', 'asc')->get();
    }

    /**
     * Queue sync job for an account.
     */
    private function syncAccount(GoogleAccount $account): void
    {
        $maxResults = (int) $this->option('max-results');

        // Dispatch the sync job
        GmailSyncInboxJob::dispatch($account, $maxResults);

        Log::info('Gmail sync job dispatched', [
            'account_id' => $account->id,
            'email' => $account->email,
            'max_results' => $maxResults,
            'last_sync_at' => $account->last_sync_at?->toDateTimeString(),
            'sync_frequency' => $account->sync_frequency_minutes
        ]);
    }

    /**
     * Get account status summary for display.
     */
    private function getAccountStatusSummary(): array
    {
        return [
            'active' => GoogleAccount::where('status', 'active')->count(),
            'inactive' => GoogleAccount::where('status', '!=', 'active')->count(),
            'auto_sync_enabled' => GoogleAccount::where('auto_sync_enabled', true)->count(),
            'needs_sync' => GoogleAccount::active()
                ->where('auto_sync_enabled', true)
                ->where(function ($q) {
                    $q->whereNull('last_sync_at')
                      ->orWhereRaw('
                          last_sync_at <= DATE_SUB(NOW(), INTERVAL sync_frequency_minutes MINUTE)
                      ');
                })
                ->count(),
        ];
    }
}
