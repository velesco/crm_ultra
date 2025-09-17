<?php

namespace App\Http\Controllers;

use App\Models\GoogleAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Exception;

class GmailOAuthController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->middleware('auth');
        $this->initializeGoogleClient();
    }

    /**
     * Initialize Google Client with OAuth2 configuration.
     */
    private function initializeGoogleClient()
    {
        $this->client = new GoogleClient();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect_gmail'));
        
        // Gmail and Sheets API scopes
        $this->client->addScope([
            Gmail::GMAIL_READONLY,
            Gmail::GMAIL_SEND,
            Gmail::GMAIL_MODIFY,
            'https://www.googleapis.com/auth/spreadsheets',
            'https://www.googleapis.com/auth/spreadsheets.readonly'
        ]);
        
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    /**
     * Show Gmail OAuth management page.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user's Google accounts
        $googleAccounts = GoogleAccount::where('user_id', $user->id)
            ->with(['syncLogs' => function($query) {
                $query->latest()->limit(5);
            }])
            ->get();

        return view('settings.gmail-oauth', compact('googleAccounts'));
    }

    /**
     * Redirect to Google OAuth consent screen.
     */
    public function redirectToGoogle(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Store user info and team context in session
            session([
                'gmail_oauth_user_id' => $user->id,
                'gmail_oauth_team_id' => $request->get('team_id', $user->current_team_id),
                'gmail_oauth_visibility' => $request->get('visibility', 'private'),
            ]);

            // Generate OAuth URL
            $authUrl = $this->client->createAuthUrl();

            Log::info('Gmail OAuth redirect initiated', [
                'user_id' => $user->id,
                'team_id' => session('gmail_oauth_team_id'),
                'visibility' => session('gmail_oauth_visibility')
            ]);

            return redirect($authUrl);

        } catch (Exception $e) {
            Log::error('Gmail OAuth redirect error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to initiate Gmail OAuth. Please try again.');
        }
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleCallback(Request $request)
    {
        try {
            $code = $request->get('code');
            $error = $request->get('error');

            // Handle OAuth denial
            if ($error || !$code) {
                Log::warning('Gmail OAuth denied or error', [
                    'error' => $error,
                    'user_id' => session('gmail_oauth_user_id')
                ]);

                return redirect()->route('settings.integrations')
                    ->with('error', 'Gmail access was denied. Please try again to enable Gmail integration.');
            }

            // Exchange code for tokens
            $this->client->authenticate($code);
            $token = $this->client->getAccessToken();

            if (!$token) {
                throw new Exception('Failed to obtain access token from Google');
            }

            // Get user profile from Gmail API
            $gmail = new Gmail($this->client);
            $profile = $gmail->users->getProfile('me');

            // Create or update Google account
            $googleAccount = $this->createOrUpdateGoogleAccount([
                'email' => $profile->getEmailAddress(),
                'token' => $token,
                'user_id' => session('gmail_oauth_user_id'),
                'team_id' => session('gmail_oauth_team_id'),
                'visibility' => session('gmail_oauth_visibility', 'private')
            ]);

            // Clear session data
            session()->forget(['gmail_oauth_user_id', 'gmail_oauth_team_id', 'gmail_oauth_visibility']);

            Log::info('Gmail OAuth successful', [
                'google_account_id' => $googleAccount->id,
                'email' => $googleAccount->email
            ]);

            return redirect()->route('settings.integrations')
                ->with('success', "Gmail account {$googleAccount->email} connected successfully!");

        } catch (Exception $e) {
            Log::error('Gmail OAuth callback error', [
                'error' => $e->getMessage(),
                'user_id' => session('gmail_oauth_user_id')
            ]);

            return redirect()->route('settings.integrations')
                ->with('error', 'Failed to connect Gmail account. Please try again.');
        }
    }

    /**
     * Create or update Google account.
     */
    private function createOrUpdateGoogleAccount(array $data)
    {
        $token = $data['token'];
        
        return GoogleAccount::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'email' => $data['email']
            ],
            [
                'team_id' => $data['team_id'],
                'provider' => 'google',
                'scopes' => $token['scope'] ? explode(' ', $token['scope']) : [],
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'token_expires_at' => isset($token['expires_in']) ? now()->addSeconds($token['expires_in']) : null,
                'visibility' => $data['visibility'],
                'status' => 'active',
                'sync_settings' => [
                    'sync_inbox' => true,
                    'sync_sent' => true,
                    'sync_drafts' => false,
                    'auto_create_contacts' => true,
                    'days_back_initial_sync' => 30
                ],
                'auto_sync_enabled' => true,
                'sync_frequency_minutes' => 15
            ]
        );
    }

    /**
     * Disconnect Gmail account.
     */
    public function disconnect(GoogleAccount $googleAccount)
    {
        try {
            // Verify ownership
            if ($googleAccount->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Revoke token at Google
            if ($googleAccount->access_token) {
                try {
                    $this->client->revokeToken($googleAccount->access_token);
                } catch (Exception $e) {
                    Log::warning('Failed to revoke Google token', [
                        'google_account_id' => $googleAccount->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $email = $googleAccount->email;
            
            // Soft delete - keep emails but mark account as inactive
            $googleAccount->update([
                'status' => 'disconnected',
                'access_token' => null,
                'refresh_token' => null,
                'token_expires_at' => null
            ]);

            Log::info('Gmail account disconnected', [
                'google_account_id' => $googleAccount->id,
                'email' => $email
            ]);

            return response()->json([
                'success' => true,
                'message' => "Gmail account {$email} disconnected successfully"
            ]);

        } catch (Exception $e) {
            Log::error('Gmail disconnect error', [
                'google_account_id' => $googleAccount->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to disconnect Gmail account'], 500);
        }
    }

    /**
     * Reconnect Gmail account.
     */
    public function reconnect(GoogleAccount $googleAccount)
    {
        try {
            // Verify ownership
            if ($googleAccount->user_id !== Auth::id()) {
                abort(403);
            }

            // Store reconnection context
            session([
                'gmail_reconnect_account_id' => $googleAccount->id,
                'gmail_oauth_user_id' => $googleAccount->user_id,
                'gmail_oauth_team_id' => $googleAccount->team_id,
                'gmail_oauth_visibility' => $googleAccount->visibility,
            ]);

            // Generate OAuth URL
            $authUrl = $this->client->createAuthUrl();

            return redirect($authUrl);

        } catch (Exception $e) {
            Log::error('Gmail reconnect error', [
                'google_account_id' => $googleAccount->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to reconnect Gmail account.');
        }
    }

    /**
     * Refresh access token.
     */
    public function refreshToken(GoogleAccount $googleAccount)
    {
        try {
            if (!$googleAccount->refresh_token) {
                return response()->json(['error' => 'No refresh token available'], 400);
            }

            $this->client->refreshToken($googleAccount->refresh_token);
            $token = $this->client->getAccessToken();

            $googleAccount->update([
                'access_token' => $token['access_token'],
                'token_expires_at' => isset($token['expires_in']) ? now()->addSeconds($token['expires_in']) : null,
                'status' => 'active'
            ]);

            Log::info('Gmail token refreshed', [
                'google_account_id' => $googleAccount->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'expires_at' => $googleAccount->token_expires_at
            ]);

        } catch (Exception $e) {
            Log::error('Gmail token refresh error', [
                'google_account_id' => $googleAccount->id,
                'error' => $e->getMessage()
            ]);

            // Mark as needs reauth
            $googleAccount->update(['status' => 'token_expired']);

            return response()->json(['error' => 'Token refresh failed. Reconnection required.'], 400);
        }
    }

    /**
     * Get account status and statistics.
     */
    public function status(GoogleAccount $googleAccount)
    {
        try {
            // Verify ownership
            if ($googleAccount->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $stats = [
                'total_emails' => $googleAccount->emails()->count(),
                'unread_emails' => $googleAccount->emails()->where('is_read', false)->count(),
                'last_sync' => $googleAccount->last_sync_at,
                'sync_status' => $googleAccount->status,
                'token_expires_at' => $googleAccount->token_expires_at,
                'needs_reauth' => $googleAccount->isTokenExpired(),
                'auto_sync_enabled' => $googleAccount->auto_sync_enabled,
                'sync_frequency' => $googleAccount->sync_frequency_minutes,
                'recent_sync_logs' => $googleAccount->recentSyncLogs(5)
            ];

            return response()->json($stats);

        } catch (Exception $e) {
            Log::error('Gmail status check error', [
                'google_account_id' => $googleAccount->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to get account status'], 500);
        }
    }

    /**
     * Update sync settings.
     */
    public function updateSyncSettings(Request $request, GoogleAccount $googleAccount)
    {
        try {
            // Verify ownership
            if ($googleAccount->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'auto_sync_enabled' => 'boolean',
                'sync_frequency_minutes' => 'integer|min:5|max:1440',
                'sync_settings' => 'array',
                'sync_settings.sync_inbox' => 'boolean',
                'sync_settings.sync_sent' => 'boolean',
                'sync_settings.sync_drafts' => 'boolean',
                'sync_settings.auto_create_contacts' => 'boolean',
                'sync_settings.days_back_initial_sync' => 'integer|min:1|max:365',
            ]);

            $googleAccount->update($validated);

            Log::info('Gmail sync settings updated', [
                'google_account_id' => $googleAccount->id,
                'settings' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sync settings updated successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Gmail sync settings update error', [
                'google_account_id' => $googleAccount->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to update sync settings'], 500);
        }
    }
}
