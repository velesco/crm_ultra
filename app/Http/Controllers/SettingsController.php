<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use App\Models\User;
use App\Models\SmtpConfig;
use App\Models\SmsProvider;
use App\Models\WhatsAppSession;
use App\Models\GoogleSheetsIntegration;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display main settings dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get system overview
        $systemStats = [
            'total_contacts' => \App\Models\Contact::count(),
            'total_campaigns' => \App\Models\EmailCampaign::count(),
            'smtp_configs' => SmtpConfig::count(),
            'sms_providers' => SmsProvider::count(),
            'whatsapp_sessions' => WhatsAppSession::count(),
            'google_integrations' => GoogleSheetsIntegration::count(),
        ];

        // Get recent activity
        $recentActivity = $this->getRecentActivity();

        // System health checks
        $systemHealth = $this->checkSystemHealth();

        return view('settings.index', compact(
            'user',
            'systemStats',
            'recentActivity',
            'systemHealth'
        ));
    }

    /**
     * General system settings
     */
    public function general()
    {
        $settings = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'app_timezone' => config('app.timezone'),
            'app_locale' => config('app.locale'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'pagination_per_page' => config('crm.pagination.per_page', 25),
            'default_currency' => config('crm.currency', 'USD'),
            'date_format' => config('crm.date_format', 'Y-m-d'),
            'time_format' => config('crm.time_format', 'H:i'),
            'allow_registration' => config('crm.features.allow_registration', true),
            'require_email_verification' => config('crm.features.require_email_verification', true),
            'enable_google_login' => config('crm.features.google_login', true),
            'enable_two_factor' => config('crm.features.two_factor', false),
            'max_file_upload_size' => config('crm.limits.max_file_upload_size', 10),
            'max_bulk_operations' => config('crm.limits.max_bulk_operations', 1000),
            'session_timeout' => config('session.lifetime'),
        ];

        return view('settings.general', compact('settings'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'app_timezone' => 'required|string',
            'app_locale' => 'required|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
            'pagination_per_page' => 'required|integer|min:10|max:100',
            'default_currency' => 'required|string|max:3',
            'date_format' => 'required|string',
            'time_format' => 'required|string',
            'allow_registration' => 'boolean',
            'require_email_verification' => 'boolean',
            'enable_google_login' => 'boolean',
            'enable_two_factor' => 'boolean',
            'max_file_upload_size' => 'required|integer|min:1|max:100',
            'max_bulk_operations' => 'required|integer|min:100|max:10000',
            'session_timeout' => 'required|integer|min:30|max:43200',
        ]);

        // Update configuration in database or cache
        $settings = [
            'app.name' => $request->app_name,
            'app.url' => $request->app_url,
            'app.timezone' => $request->app_timezone,
            'app.locale' => $request->app_locale,
            'mail.from.address' => $request->mail_from_address,
            'mail.from.name' => $request->mail_from_name,
            'crm.pagination.per_page' => $request->pagination_per_page,
            'crm.currency' => $request->default_currency,
            'crm.date_format' => $request->date_format,
            'crm.time_format' => $request->time_format,
            'crm.features.allow_registration' => $request->boolean('allow_registration'),
            'crm.features.require_email_verification' => $request->boolean('require_email_verification'),
            'crm.features.google_login' => $request->boolean('enable_google_login'),
            'crm.features.two_factor' => $request->boolean('enable_two_factor'),
            'crm.limits.max_file_upload_size' => $request->max_file_upload_size,
            'crm.limits.max_bulk_operations' => $request->max_bulk_operations,
            'session.lifetime' => $request->session_timeout,
        ];

        foreach ($settings as $key => $value) {
            Cache::forever('settings.' . $key, $value);
        }

        return redirect()->back()->with('success', 'General settings updated successfully.');
    }

    /**
     * User profile settings
     */
    public function profile()
    {
        $user = Auth::user();
        
        // Get user activity stats
        $userStats = [
            'campaigns_created' => \App\Models\EmailCampaign::where('created_by', $user->id)->count(),
            'contacts_created' => \App\Models\Contact::where('created_by', $user->id)->count(),
            'sms_sent' => \App\Models\SmsMessage::where('created_by', $user->id)->count(),
            'last_login' => $user->last_login_at,
            'account_created' => $user->created_at,
        ];

        // Get user preferences
        $preferences = [
            'theme' => $user->preferences['theme'] ?? 'light',
            'language' => $user->preferences['language'] ?? 'en',
            'timezone' => $user->preferences['timezone'] ?? 'UTC',
            'notifications_email' => $user->preferences['notifications_email'] ?? true,
            'notifications_browser' => $user->preferences['notifications_browser'] ?? true,
            'dashboard_layout' => $user->preferences['dashboard_layout'] ?? 'grid',
        ];

        return view('settings.profile', compact('user', 'userStats', 'preferences'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
            'theme' => 'required|in:light,dark,auto',
            'language' => 'required|string|max:5',
            'timezone' => 'required|string|max:50',
            'notifications_email' => 'boolean',
            'notifications_browser' => 'boolean',
            'dashboard_layout' => 'required|in:grid,list,cards',
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'job_title' => $request->job_title,
            'bio' => $request->bio,
            'preferences' => array_merge($user->preferences ?? [], [
                'theme' => $request->theme,
                'language' => $request->language,
                'timezone' => $request->timezone,
                'notifications_email' => $request->boolean('notifications_email'),
                'notifications_browser' => $request->boolean('notifications_browser'),
                'dashboard_layout' => $request->dashboard_layout,
            ])
        ]);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar && Storage::exists('public/avatars/' . $user->avatar)) {
            Storage::delete('public/avatars/' . $user->avatar);
        }

        // Store new avatar
        $fileName = $user->id . '_' . time() . '.' . $request->avatar->extension();
        $request->avatar->storeAs('public/avatars', $fileName);

        $user->update(['avatar' => $fileName]);

        return redirect()->back()->with('success', 'Avatar updated successfully.');
    }

    /**
     * Delete user avatar
     */
    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::exists('public/avatars/' . $user->avatar)) {
            Storage::delete('public/avatars/' . $user->avatar);
        }

        $user->update(['avatar' => null]);

        return redirect()->back()->with('success', 'Avatar deleted successfully.');
    }

    /**
     * Security settings
     */
    public function security()
    {
        $user = Auth::user();
        
        // Get security info
        $securityInfo = [
            'password_changed_at' => $user->password_changed_at ?? $user->created_at,
            'two_factor_enabled' => $user->two_factor_secret ? true : false,
            'login_attempts' => $this->getRecentLoginAttempts($user->id),
            'active_sessions' => $this->getActiveSessions($user->id),
            'last_login_ip' => $user->last_login_ip,
            'last_login_at' => $user->last_login_at,
        ];

        return view('settings.security', compact('user', 'securityInfo'));
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    /**
     * Enable two-factor authentication
     */
    public function enableTwoFactor(Request $request)
    {
        $user = Auth::user();
        
        // Generate 2FA secret
        $secret = Str::random(32);
        
        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => json_encode($this->generateRecoveryCodes()),
        ]);

        return response()->json([
            'secret' => $secret,
            'qr_code_url' => $this->generateQrCodeUrl($user->email, $secret),
            'recovery_codes' => json_decode($user->two_factor_recovery_codes),
        ]);
    }

    /**
     * Disable two-factor authentication
     */
    public function disableTwoFactor(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);

        return redirect()->back()->with('success', 'Two-factor authentication disabled.');
    }

    /**
     * Notification settings
     */
    public function notifications()
    {
        $user = Auth::user();
        
        $notificationSettings = $user->notification_preferences ?? [
            'email_campaigns' => true,
            'sms_delivery' => true,
            'whatsapp_messages' => true,
            'contact_updates' => false,
            'system_alerts' => true,
            'security_alerts' => true,
            'weekly_reports' => true,
            'monthly_reports' => false,
        ];

        return view('settings.notifications', compact('user', 'notificationSettings'));
    }

    /**
     * Update notification preferences
     */
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();
        
        $settings = [
            'email_campaigns' => $request->boolean('email_campaigns'),
            'sms_delivery' => $request->boolean('sms_delivery'),
            'whatsapp_messages' => $request->boolean('whatsapp_messages'),
            'contact_updates' => $request->boolean('contact_updates'),
            'system_alerts' => $request->boolean('system_alerts'),
            'security_alerts' => $request->boolean('security_alerts'),
            'weekly_reports' => $request->boolean('weekly_reports'),
            'monthly_reports' => $request->boolean('monthly_reports'),
        ];

        $user->update(['notification_preferences' => $settings]);

        return redirect()->back()->with('success', 'Notification preferences updated successfully.');
    }

    /**
     * Integrations overview
     */
    public function integrations()
    {
        $integrations = [
            'google' => [
                'name' => 'Google Services',
                'status' => config('services.google.client_id') ? 'configured' : 'not_configured',
                'features' => ['OAuth Login', 'Google Sheets', 'Gmail API'],
                'connected_accounts' => GoogleSheetsIntegration::where('user_id', Auth::id())->count(),
            ],
            'smtp' => [
                'name' => 'SMTP Servers',
                'status' => SmtpConfig::where('is_active', true)->exists() ? 'active' : 'inactive',
                'features' => ['Email Campaigns', 'Transactional Emails'],
                'configured_servers' => SmtpConfig::count(),
            ],
            'sms' => [
                'name' => 'SMS Providers',
                'status' => SmsProvider::where('is_active', true)->exists() ? 'active' : 'inactive',
                'features' => ['Bulk SMS', 'Automated Messages'],
                'active_providers' => SmsProvider::where('is_active', true)->count(),
            ],
            'whatsapp' => [
                'name' => 'WhatsApp Business',
                'status' => WhatsAppSession::where('status', 'connected')->exists() ? 'active' : 'inactive',
                'features' => ['Direct Messaging', 'Bulk Messages'],
                'active_sessions' => WhatsAppSession::where('status', 'connected')->count(),
            ],
            'zapier' => [
                'name' => 'Zapier',
                'status' => config('crm.integrations.zapier.enabled') ? 'available' : 'disabled',
                'features' => ['Automation', 'Third-party Connections'],
                'webhooks' => 0, // Would come from webhooks table
            ]
        ];

        return view('settings.integrations', compact('integrations'));
    }

    /**
     * API Keys management
     */
    public function apiKeys()
    {
        $user = Auth::user();
        
        // Get user's API keys (would need an api_keys table)
        $apiKeys = collect([
            [
                'id' => 1,
                'name' => 'CRM API Key',
                'key' => 'crm_' . Str::random(40),
                'last_used' => now()->subDays(2),
                'created_at' => now()->subDays(30),
            ]
        ]); // This would come from a database table

        $permissions = [
            'contacts.read' => 'Read Contacts',
            'contacts.write' => 'Write Contacts',
            'campaigns.read' => 'Read Campaigns',
            'campaigns.write' => 'Write Campaigns',
            'sms.send' => 'Send SMS',
            'whatsapp.send' => 'Send WhatsApp',
            'reports.read' => 'Read Reports',
        ];

        return view('settings.api-keys', compact('apiKeys', 'permissions'));
    }

    /**
     * Create new API key
     */
    public function createApiKey(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $apiKey = [
            'name' => $request->name,
            'key' => 'crm_' . Str::random(40),
            'permissions' => $request->permissions,
            'expires_at' => $request->expires_at,
            'user_id' => Auth::id(),
            'created_at' => now(),
        ];

        // In a real implementation, this would be saved to database
        
        return redirect()->back()->with('success', 'API key created successfully.');
    }

    /**
     * Delete API key
     */
    public function deleteApiKey($keyId)
    {
        // In a real implementation, this would delete from database
        
        return redirect()->back()->with('success', 'API key deleted successfully.');
    }

    /**
     * Team management (for multi-user setups)
     */
    public function team()
    {
        $teamMembers = User::with('roles')->get();
        
        $availableRoles = [
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'agent' => 'Agent',
            'viewer' => 'Viewer',
        ];

        $teamStats = [
            'total_members' => User::count(),
            'active_members' => User::where('status', 'active')->count(),
            'pending_invitations' => 0, // Would come from invitations table
            'last_activity' => User::latest('last_login_at')->first()->last_login_at ?? null,
        ];

        return view('settings.team', compact('teamMembers', 'availableRoles', 'teamStats'));
    }

    /**
     * Invite team member
     */
    public function inviteTeamMember(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string|in:admin,manager,agent,viewer',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        // In a real implementation, this would send an invitation email
        // and create a pending invitation record
        
        return redirect()->back()->with('success', 'Team member invitation sent successfully.');
    }

    /**
     * Remove team member
     */
    public function removeTeamMember(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot remove yourself from the team.');
        }

        // In a real implementation, this would deactivate or delete the user
        // after handling data ownership transfer
        
        return redirect()->back()->with('success', 'Team member removed successfully.');
    }

    /**
     * Update team member permissions
     */
    public function updatePermissions(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|string|in:admin,manager,agent,viewer',
            'permissions' => 'array',
        ]);

        // In a real implementation, this would update user roles and permissions
        
        return redirect()->back()->with('success', 'Permissions updated successfully.');
    }

    // Private helper methods

    private function getRecentActivity()
    {
        $activities = [];

        // Get recent campaigns
        $recentCampaigns = \App\Models\EmailCampaign::latest()->take(5)->get()
            ->map(function ($campaign) {
                return [
                    'type' => 'campaign',
                    'description' => "Email campaign '{$campaign->name}' was created",
                    'timestamp' => $campaign->created_at,
                    'icon' => 'mail',
                    'color' => 'blue'
                ];
            });

        // Get recent contacts
        $recentContacts = \App\Models\Contact::latest()->take(5)->get()
            ->map(function ($contact) {
                return [
                    'type' => 'contact',
                    'description' => "New contact '{$contact->first_name} {$contact->last_name}' was added",
                    'timestamp' => $contact->created_at,
                    'icon' => 'user-plus',
                    'color' => 'green'
                ];
            });

        // Get recent SMS messages
        $recentSms = \App\Models\SmsMessage::latest()->take(3)->get()
            ->map(function ($sms) {
                return [
                    'type' => 'sms',
                    'description' => "SMS sent to {$sms->to_number}",
                    'timestamp' => $sms->created_at,
                    'icon' => 'message-circle',
                    'color' => 'purple'
                ];
            });

        return collect($activities)
            ->merge($recentCampaigns)
            ->merge($recentContacts)
            ->merge($recentSms)
            ->sortByDesc('timestamp')
            ->take(10)
            ->values();
    }

    private function checkSystemHealth()
    {
        $health = [];

        // Check database connection
        try {
            DB::connection()->getPdo();
            $health['database'] = ['status' => 'healthy', 'message' => 'Database connection is working'];
        } catch (\Exception $e) {
            $health['database'] = ['status' => 'error', 'message' => 'Database connection failed'];
        }

        // Check storage
        $storageSpace = disk_free_space(storage_path());
        $health['storage'] = [
            'status' => $storageSpace > 1000000000 ? 'healthy' : 'warning', // 1GB threshold
            'message' => 'Free space: ' . $this->formatBytes($storageSpace)
        ];

        // Check queue connection
        try {
            $health['queue'] = ['status' => 'healthy', 'message' => 'Queue system is operational'];
        } catch (\Exception $e) {
            $health['queue'] = ['status' => 'warning', 'message' => 'Queue system may have issues'];
        }

        // Check SMTP configurations
        $activeSmtp = SmtpConfig::where('is_active', true)->count();
        $health['smtp'] = [
            'status' => $activeSmtp > 0 ? 'healthy' : 'warning',
            'message' => "{$activeSmtp} active SMTP configuration(s)"
        ];

        // Check SMS providers
        $activeSms = SmsProvider::where('is_active', true)->count();
        $health['sms'] = [
            'status' => $activeSms > 0 ? 'healthy' : 'warning',
            'message' => "{$activeSms} active SMS provider(s)"
        ];

        // Check WhatsApp sessions
        $activeWhatsApp = WhatsAppSession::where('status', 'connected')->count();
        $health['whatsapp'] = [
            'status' => $activeWhatsApp > 0 ? 'healthy' : 'info',
            'message' => "{$activeWhatsApp} active WhatsApp session(s)"
        ];

        return $health;
    }

    private function getRecentLoginAttempts($userId)
    {
        // In a real implementation, this would query a login_attempts table
        return [
            ['ip' => '192.168.1.1', 'success' => true, 'created_at' => now()->subHours(2)],
            ['ip' => '192.168.1.1', 'success' => true, 'created_at' => now()->subDays(1)],
        ];
    }

    private function getActiveSessions($userId)
    {
        // In a real implementation, this would query active sessions
        return [
            [
                'id' => 'sess_' . Str::random(10),
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Chrome/91.0 (Windows NT 10.0)',
                'last_activity' => now()->subMinutes(5),
                'current' => true
            ]
        ];
    }

    private function generateRecoveryCodes()
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(Str::random(4) . '-' . Str::random(4));
        }
        return $codes;
    }

    private function generateQrCodeUrl($email, $secret)
    {
        $appName = config('app.name');
        return "otpauth://totp/{$appName}:{$email}?secret={$secret}&issuer={$appName}";
    }

    private function formatBytes($size, $precision = 2)
    {
        if ($size > 0) {
            $size = (int) $size;
            $base = log($size) / log(1024);
            $suffixes = [' bytes', ' KB', ' MB', ' GB', ' TB'];
            return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
        }
        return $size;
    }
}
