<?php

namespace App\Http\Controllers;

use App\Models\GoogleAccount;
use App\Models\Email;
use App\Services\GmailService;
use App\Jobs\GmailSyncInboxJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

class GmailInboxController extends Controller
{
    private $gmailService;

    public function __construct(GmailService $gmailService)
    {
        $this->middleware('auth');
        $this->gmailService = $gmailService;
    }

    /**
     * Display the unified Gmail inbox.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get user's Google accounts
        $googleAccounts = GoogleAccount::where('user_id', $user->id)
                                     ->active()
                                     ->get();

        // Build email query with filters
        $emailsQuery = $this->buildEmailQuery($request, $googleAccounts);

        // Paginate results
        $emails = $emailsQuery->paginate(25)->withQueryString();

        // Get inbox statistics
        $stats = $this->getInboxStats($googleAccounts);

        return view('gmail.inbox', compact('emails', 'googleAccounts', 'stats'));
    }

    /**
     * Get email details for modal view.
     */
    public function show(Request $request, Email $email)
    {
        try {
            // Verify user owns this email's Google account
            if ($email->googleAccount->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to email'
                ], 403);
            }

            // Mark as read if requested
            if (!$email->is_read) {
                $email->markAsRead();
                
                // Also mark as read in Gmail if possible
                $this->gmailService->markAsRead($email->googleAccount, $email->gmail_id, true);
            }

            // Get thread emails (conversation)
            $threadEmails = $email->threadEmails()->orderBy('date_sent', 'asc')->get();

            // Load attachments
            $attachments = $email->attachments()->get();

            $html = view('gmail.partials.email-detail', compact('email', 'threadEmails', 'attachments'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'email' => $email->toArray()
            ]);

        } catch (Exception $e) {
            Log::error('Failed to load email details', [
                'email_id' => $email->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark emails as read.
     */
    public function markAsRead(Request $request)
    {
        try {
            $validated = $request->validate([
                'email_ids' => 'required|array',
                'email_ids.*' => 'integer|exists:emails,id'
            ]);

            $user = Auth::user();
            $successCount = 0;
            $errorCount = 0;

            foreach ($validated['email_ids'] as $emailId) {
                try {
                    $email = Email::whereHas('googleAccount', function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })->findOrFail($emailId);

                    // Mark as read locally
                    $email->markAsRead();

                    // Mark as read in Gmail
                    $success = $this->gmailService->markAsRead($email->googleAccount, $email->gmail_id, true);
                    
                    if ($success) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }

                } catch (Exception $e) {
                    $errorCount++;
                    Log::warning('Failed to mark email as read', [
                        'email_id' => $emailId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Marked {$successCount} emails as read" . ($errorCount > 0 ? " ({$errorCount} failed)" : ''),
                'processed' => $successCount,
                'failed' => $errorCount
            ]);

        } catch (Exception $e) {
            Log::error('Bulk mark as read failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark emails as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Star/unstar emails.
     */
    public function starEmails(Request $request)
    {
        try {
            $validated = $request->validate([
                'email_ids' => 'required|array',
                'email_ids.*' => 'integer|exists:emails,id'
            ]);

            $user = Auth::user();
            $successCount = 0;
            $errorCount = 0;

            foreach ($validated['email_ids'] as $emailId) {
                try {
                    $email = Email::whereHas('googleAccount', function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })->findOrFail($emailId);

                    // Toggle star status locally
                    $email->toggleStar();

                    $successCount++;

                } catch (Exception $e) {
                    $errorCount++;
                    Log::warning('Failed to star email', [
                        'email_id' => $emailId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Updated {$successCount} emails" . ($errorCount > 0 ? " ({$errorCount} failed)" : ''),
                'processed' => $successCount,
                'failed' => $errorCount
            ]);

        } catch (Exception $e) {
            Log::error('Bulk star failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update emails: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle star status for single email.
     */
    public function toggleStar(Request $request, Email $email)
    {
        try {
            // Verify user owns this email's Google account
            if ($email->googleAccount->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Toggle star status
            $email->toggleStar();

            return response()->json([
                'success' => true,
                'starred' => $email->is_starred,
                'message' => $email->is_starred ? 'Email starred' : 'Star removed'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to toggle star', [
                'email_id' => $email->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle star'
            ], 500);
        }
    }

    /**
     * Mark single email as read.
     */
    public function markEmailAsRead(Request $request, Email $email)
    {
        try {
            // Verify user owns this email's Google account
            if ($email->googleAccount->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Mark as read locally
            $email->markAsRead();

            // Mark as read in Gmail
            $this->gmailService->markAsRead($email->googleAccount, $email->gmail_id, true);

            return response()->json([
                'success' => true,
                'message' => 'Email marked as read'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to mark email as read', [
                'email_id' => $email->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as read'
            ], 500);
        }
    }

    /**
     * Sync all Gmail accounts.
     */
    public function syncAll(Request $request)
    {
        try {
            $user = Auth::user();
            
            $accounts = GoogleAccount::where('user_id', $user->id)
                                   ->active()
                                   ->where('auto_sync_enabled', true)
                                   ->get();

            if ($accounts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active Gmail accounts found for sync'
                ]);
            }

            $jobsQueued = 0;
            foreach ($accounts as $account) {
                GmailSyncInboxJob::dispatch($account, 50);
                $jobsQueued++;
            }

            Log::info('Manual Gmail sync initiated', [
                'user_id' => $user->id,
                'accounts_synced' => $jobsQueued
            ]);

            return response()->json([
                'success' => true,
                'message' => "Sync initiated for {$jobsQueued} Gmail accounts",
                'accounts_synced' => $jobsQueued
            ]);

        } catch (Exception $e) {
            Log::error('Manual sync all failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate sync: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build email query with filters.
     */
    private function buildEmailQuery(Request $request, $googleAccounts)
    {
        $query = Email::whereIn('google_account_id', $googleAccounts->pluck('id'))
                     ->inbox() // Exclude trash and spam
                     ->with(['googleAccount', 'attachments'])
                     ->orderBy('date_received', 'desc');

        // Account filter
        if ($request->filled('account')) {
            $query->where('google_account_id', $request->account);
        }

        // Status filters
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'unread':
                    $query->unread();
                    break;
                case 'read':
                    $query->where('is_read', true);
                    break;
                case 'starred':
                    $query->starred();
                    break;
                case 'important':
                    $query->important();
                    break;
            }
        }

        // Label filter
        if ($request->filled('label')) {
            $query->withLabel($request->label);
        }

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        return $query;
    }

    /**
     * Get inbox statistics.
     */
    private function getInboxStats($googleAccounts)
    {
        $accountIds = $googleAccounts->pluck('id');

        return [
            'total_emails' => Email::whereIn('google_account_id', $accountIds)->inbox()->count(),
            'unread_emails' => Email::whereIn('google_account_id', $accountIds)->inbox()->unread()->count(),
            'starred_emails' => Email::whereIn('google_account_id', $accountIds)->inbox()->starred()->count(),
            'important_emails' => Email::whereIn('google_account_id', $accountIds)->inbox()->important()->count(),
        ];
    }
}
