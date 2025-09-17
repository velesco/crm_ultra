<?php

namespace App\Http\Controllers;

use App\Models\GoogleAccount;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class GmailTeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display team Gmail management.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $currentTeam = $user->currentTeam ?? $user->ownedTeams()->first();

        if (!$currentTeam) {
            return redirect()->route('teams.create')
                ->with('error', 'You need to create or join a team first.');
        }

        // Get team Gmail accounts
        $teamAccounts = GoogleAccount::forTeam($currentTeam->id)
            ->with(['user', 'syncLogs' => function($query) {
                $query->latest()->limit(3);
            }])
            ->get();

        // Get team members
        $teamMembers = $currentTeam->users()->with(['googleAccounts' => function($query) use ($currentTeam) {
            $query->where('team_id', $currentTeam->id)->orWhere('visibility', 'team');
        }])->get();

        // Statistics
        $stats = [
            'total_accounts' => $teamAccounts->count(),
            'active_accounts' => $teamAccounts->where('status', 'active')->count(),
            'total_emails' => $teamAccounts->sum(function($account) {
                return $account->emails()->count();
            }),
            'unread_emails' => $teamAccounts->sum(function($account) {
                return $account->getUnreadEmailsCount();
            }),
        ];

        return view('settings.gmail-team', compact(
            'currentTeam',
            'teamAccounts', 
            'teamMembers',
            'stats'
        ));
    }

    /**
     * Update account visibility settings.
     */
    public function updateVisibility(Request $request, GoogleAccount $googleAccount)
    {
        // Check if user can manage this account
        if (!$this->canManageAccount($googleAccount)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'visibility' => 'required|in:private,team,public',
            'team_id' => 'nullable|exists:teams,id'
        ]);

        $originalVisibility = $googleAccount->visibility;
        
        $googleAccount->update($validated);

        Log::info('Gmail account visibility updated', [
            'google_account_id' => $googleAccount->id,
            'original_visibility' => $originalVisibility,
            'new_visibility' => $validated['visibility'],
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visibility settings updated successfully',
            'account' => $googleAccount->fresh()
        ]);
    }

    /**
     * Grant team member access to Gmail account.
     */
    public function grantAccess(Request $request, GoogleAccount $googleAccount)
    {
        if (!$this->canManageAccount($googleAccount)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'permissions' => 'required|array',
            'permissions.*' => 'in:view,send,manage'
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Check if user is in the same team
        $currentTeam = Auth::user()->currentTeam;
        if (!$currentTeam || !$currentTeam->users->contains($user)) {
            return response()->json(['error' => 'User is not in your team'], 400);
        }

        // Update account permissions (stored in custom fields or separate table)
        $permissions = $googleAccount->custom_permissions ?? [];
        $permissions[$user->id] = [
            'granted_by' => Auth::id(),
            'granted_at' => now()->toISOString(),
            'permissions' => $validated['permissions']
        ];

        $googleAccount->update(['custom_permissions' => $permissions]);

        Log::info('Gmail account access granted', [
            'google_account_id' => $googleAccount->id,
            'granted_to' => $user->id,
            'permissions' => $validated['permissions'],
            'granted_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Access granted to {$user->name}",
            'user' => $user,
            'permissions' => $validated['permissions']
        ]);
    }

    /**
     * Revoke team member access from Gmail account.
     */
    public function revokeAccess(Request $request, GoogleAccount $googleAccount, User $user)
    {
        if (!$this->canManageAccount($googleAccount)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $permissions = $googleAccount->custom_permissions ?? [];
        unset($permissions[$user->id]);

        $googleAccount->update(['custom_permissions' => $permissions]);

        Log::info('Gmail account access revoked', [
            'google_account_id' => $googleAccount->id,
            'revoked_from' => $user->id,
            'revoked_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Access revoked from {$user->name}"
        ]);
    }

    /**
     * Get team Gmail statistics.
     */
    public function getTeamStats(Request $request)
    {
        $user = Auth::user();
        $currentTeam = $user->currentTeam;

        if (!$currentTeam) {
            return response()->json(['error' => 'No team selected'], 400);
        }

        // Get all accessible accounts for the team
        $accounts = $this->getAccessibleAccounts($user, $currentTeam);

        $stats = [
            'team_id' => $currentTeam->id,
            'team_name' => $currentTeam->name,
            'accounts_summary' => [
                'total' => $accounts->count(),
                'active' => $accounts->where('status', 'active')->count(),
                'needs_auth' => $accounts->where('status', 'token_expired')->count(),
                'disconnected' => $accounts->where('status', 'disconnected')->count()
            ],
            'email_summary' => [
                'total_emails' => $accounts->sum(function($account) {
                    return $account->emails()->count();
                }),
                'unread_emails' => $accounts->sum(function($account) {
                    return $account->getUnreadEmailsCount();
                }),
                'recent_emails' => $accounts->flatMap(function($account) {
                    return $account->emails()->latest('date_received')->limit(5)->get();
                })->sortByDesc('date_received')->take(10)->values()
            ],
            'sync_activity' => [
                'last_24h' => $accounts->flatMap(function($account) {
                    return $account->syncLogs()->where('started_at', '>=', now()->subDay())->get();
                })->count(),
                'successful_syncs' => $accounts->flatMap(function($account) {
                    return $account->syncLogs()->where('status', 'success')->where('started_at', '>=', now()->subWeek())->get();
                })->count(),
                'failed_syncs' => $accounts->flatMap(function($account) {
                    return $account->syncLogs()->where('status', 'failed')->where('started_at', '>=', now()->subWeek())->get();
                })->count(),
            ],
            'members_with_access' => $currentTeam->users()->with(['googleAccounts' => function($query) use ($currentTeam) {
                $query->where('team_id', $currentTeam->id)->orWhere('visibility', 'team');
            }])->get()->map(function($member) {
                return [
                    'user' => $member->only(['id', 'name', 'email']),
                    'accounts_count' => $member->googleAccounts->count(),
                    'active_accounts' => $member->googleAccounts->where('status', 'active')->count()
                ];
            })
        ];

        return response()->json($stats);
    }

    /**
     * Update team sync settings.
     */
    public function updateTeamSyncSettings(Request $request)
    {
        $user = Auth::user();
        $currentTeam = $user->currentTeam;

        if (!$currentTeam || !Gate::allows('manage-team', $currentTeam)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'auto_sync_enabled' => 'boolean',
            'sync_frequency_minutes' => 'integer|min:5|max:1440',
            'default_visibility' => 'in:private,team',
            'allow_member_connections' => 'boolean',
            'contact_sharing_enabled' => 'boolean',
        ]);

        // Update team settings (could be stored in team meta or separate table)
        $teamSettings = $currentTeam->gmail_settings ?? [];
        $teamSettings = array_merge($teamSettings, $validated);
        $currentTeam->update(['gmail_settings' => $teamSettings]);

        Log::info('Team Gmail settings updated', [
            'team_id' => $currentTeam->id,
            'settings' => $validated,
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team settings updated successfully',
            'settings' => $teamSettings
        ]);
    }

    /**
     * Get accessible Gmail accounts for user and team.
     */
    private function getAccessibleAccounts(User $user, $team)
    {
        return GoogleAccount::where(function($query) use ($user, $team) {
            $query->where('user_id', $user->id)
                  ->orWhere(function($subQuery) use ($team) {
                      $subQuery->where('team_id', $team->id)
                               ->where('visibility', 'team');
                  });
        })->with(['user', 'emails', 'syncLogs'])->get();
    }

    /**
     * Check if user can manage the account.
     */
    private function canManageAccount(GoogleAccount $googleAccount): bool
    {
        $user = Auth::user();

        // Owner can always manage
        if ($googleAccount->user_id === $user->id) {
            return true;
        }

        // Team admin can manage team accounts
        if ($user->currentTeam && 
            $googleAccount->team_id === $user->currentTeam->id &&
            Gate::allows('manage-team', $user->currentTeam)) {
            return true;
        }

        return false;
    }

    /**
     * Export team Gmail settings.
     */
    public function exportTeamSettings(Request $request)
    {
        $user = Auth::user();
        $currentTeam = $user->currentTeam;

        if (!$currentTeam || !Gate::allows('manage-team', $currentTeam)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $accounts = GoogleAccount::forTeam($currentTeam->id)->with(['user', 'syncLogs'])->get();

        $export = [
            'team' => $currentTeam->only(['id', 'name']),
            'exported_at' => now()->toISOString(),
            'exported_by' => $user->only(['id', 'name', 'email']),
            'accounts' => $accounts->map(function($account) {
                return [
                    'id' => $account->id,
                    'email' => $account->email,
                    'owner' => $account->user->name,
                    'status' => $account->status,
                    'visibility' => $account->visibility,
                    'auto_sync_enabled' => $account->auto_sync_enabled,
                    'sync_frequency_minutes' => $account->sync_frequency_minutes,
                    'last_sync_at' => $account->last_sync_at,
                    'total_emails' => $account->emails()->count(),
                    'unread_emails' => $account->getUnreadEmailsCount(),
                    'sync_settings' => $account->sync_settings,
                    'recent_sync_success' => $account->syncLogs()
                        ->where('status', 'success')
                        ->latest()
                        ->first()?->started_at
                ];
            }),
            'summary' => [
                'total_accounts' => $accounts->count(),
                'active_accounts' => $accounts->where('status', 'active')->count(),
                'total_emails' => $accounts->sum(function($acc) { return $acc->emails()->count(); }),
                'team_members' => $currentTeam->users()->count()
            ]
        ];

        return response()->json($export);
    }
}
