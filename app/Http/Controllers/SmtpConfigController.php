<?php

namespace App\Http\Controllers;

use App\Models\SmtpConfig;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SmtpConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SmtpConfig::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('host', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by provider
        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        $smtpConfigs = $query->latest()->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total' => SmtpConfig::count(),
            'active' => SmtpConfig::where('is_active', true)->count(),
            'inactive' => SmtpConfig::where('is_active', false)->count(),
            'limit_exceeded' => SmtpConfig::whereRaw('sent_today >= daily_limit')->count(),
        ];

        return view('email.smtp.index', compact('smtpConfigs', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('email.smtp.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('SMTP Store Request:', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:smtp_configs',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'encryption' => 'required|in:tls,ssl,none',
            'username' => 'required|string|max:255',
            'password' => 'required|string',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
            'provider' => 'nullable|string|max:100',
            'daily_limit' => 'required|integer|min:1|max:50000',
            'hourly_limit' => 'required|integer|min:1|max:5000',
            'is_active' => 'boolean',
            'priority' => 'required|integer|min:1|max:100',
        ]);

        \Log::info('SMTP Validation Passed:', $validated);

        // Password will be encrypted by model mutator
        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = auth()->id();

        try {
            $smtpConfig = SmtpConfig::create($validated);
            \Log::info('SMTP Created:', ['id' => $smtpConfig->id]);

            return redirect()->route('smtp-configs.index')
                ->with('success', 'SMTP configuration created successfully.');
        } catch (\Exception $e) {
            \Log::error('SMTP Creation Error:', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Error creating SMTP configuration: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SmtpConfig $smtpConfig)
    {
        // Load usage statistics for the last 30 days
        $smtpConfig->loadUsageStats();

        return view('email.smtp.show', compact('smtpConfig'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SmtpConfig $smtpConfig)
    {
        return view('email.smtp.edit', compact('smtpConfig'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SmtpConfig $smtpConfig)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('smtp_configs')->ignore($smtpConfig->id)],
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'encryption' => 'required|in:tls,ssl,none',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
            'provider' => 'nullable|string|max:100',
            'daily_limit' => 'required|integer|min:1|max:50000',
            'hourly_limit' => 'required|integer|min:1|max:5000',
            'is_active' => 'boolean',
            'priority' => 'required|integer|min:1|max:100',
        ]);

        // Only update password if provided - will be encrypted by model mutator
        if (! $request->filled('password')) {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');

        $smtpConfig->update($validated);

        return redirect()->route('smtp-configs.index')
            ->with('success', 'SMTP configuration updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SmtpConfig $smtpConfig)
    {
        // Check if this SMTP config is currently being used
        if ($smtpConfig->emailCampaigns()->where('status', 'sending')->exists()) {
            return redirect()->route('smtp-configs.index')
                ->with('error', 'Cannot delete SMTP configuration that is currently being used by active campaigns.');
        }

        $smtpConfig->delete();

        return redirect()->route('smtp-configs.index')
            ->with('success', 'SMTP configuration deleted successfully.');
    }

    /**
     * Test SMTP connection.
     */
    public function test(SmtpConfig $smtpConfig)
    {
        try {
            // Get decrypted password for debugging
            $password = $smtpConfig->password;
            
            \Log::info('SMTP Test Request Debug', [
                'smtp_id' => $smtpConfig->id,
                'name' => $smtpConfig->name,
                'host' => $smtpConfig->host,
                'port' => $smtpConfig->port,
                'encryption' => $smtpConfig->encryption,
                'username' => $smtpConfig->username,
                'password_decoded' => $password, // DEBUG: Show decoded password
                'password_length' => strlen($password ?? ''),
                'from_email' => $smtpConfig->from_email
            ]);
            
            $success = $smtpConfig->testConnection();

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'SMTP connection test successful!',
                    'debug_info' => [
                        'host' => $smtpConfig->host,
                        'port' => $smtpConfig->port,
                        'encryption' => $smtpConfig->encryption,
                        'username' => $smtpConfig->username,
                        'password_preview' => substr($password ?? '', 0, 3) . str_repeat('*', max(0, strlen($password ?? '') - 3)),
                        'password_length' => strlen($password ?? '')
                    ]
                ]);
            } else {
                // Get recent logs to help with debugging
                $recentLogs = [];
                if (function_exists('storage_path')) {
                    $logFile = storage_path('logs/laravel.log');
                    if (file_exists($logFile)) {
                        $logs = file($logFile);
                        $recentLogs = array_slice($logs, -10); // Last 10 lines
                    }
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'SMTP connection test failed. Please check your settings and logs.',
                    'debug_info' => [
                        'host' => $smtpConfig->host,
                        'port' => $smtpConfig->port,
                        'encryption' => $smtpConfig->encryption,
                        'username' => $smtpConfig->username,
                        'password_preview' => substr($password ?? '', 0, 3) . str_repeat('*', max(0, strlen($password ?? '') - 3)),
                        'password_length' => strlen($password ?? ''),
                        'password_decoded' => $password, // DEBUG: Show actual password on failure
                        'recent_logs' => array_slice($recentLogs, -3) // Last 3 log entries
                    ]
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: '.$e->getMessage(),
                'debug_info' => [
                    'error_class' => get_class($e),
                    'error_code' => $e->getCode(),
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile(),
                    'password_attempted' => $smtpConfig->password ?? 'NULL'
                ]
            ], 422);
        }
    }

    /**
     * Toggle SMTP configuration status.
     */
    public function toggle(SmtpConfig $smtpConfig)
    {
        $smtpConfig->update([
            'is_active' => ! $smtpConfig->is_active,
        ]);

        $status = $smtpConfig->is_active ? 'activated' : 'deactivated';

        return redirect()->route('smtp-configs.index')
            ->with('success', "SMTP configuration {$status} successfully.");
    }

    /**
     * Reset daily/hourly counters.
     */
    public function resetCounters(SmtpConfig $smtpConfig)
    {
        $smtpConfig->update([
            'sent_today' => 0,
            'sent_this_hour' => 0,
            'last_reset_at' => now(),
        ]);

        return redirect()->route('smtp-configs.show', $smtpConfig)
            ->with('success', 'Counters reset successfully.');
    }

    /**
     * Duplicate SMTP configuration.
     */
    public function duplicate(SmtpConfig $smtpConfig)
    {
        $duplicate = $smtpConfig->replicate();
        $duplicate->name = $smtpConfig->name.' (Copy)';
        $duplicate->is_active = false;
        $duplicate->sent_today = 0;
        $duplicate->sent_this_hour = 0;
        $duplicate->last_used_at = null;
        $duplicate->save();

        return redirect()->route('smtp-configs.edit', $duplicate)
            ->with('success', 'SMTP configuration duplicated successfully. Please review and update the settings.');
    }

    /**
     * Get SMTP providers list for dropdown.
     */
    public function getProviders()
    {
        $providers = [
            'gmail' => 'Gmail',
            'outlook' => 'Outlook/Hotmail',
            'yahoo' => 'Yahoo Mail',
            'sendgrid' => 'SendGrid',
            'mailgun' => 'Mailgun',
            'amazon_ses' => 'Amazon SES',
            'smtp2go' => 'SMTP2GO',
            'mailjet' => 'Mailjet',
            'postmark' => 'Postmark',
            'sparkpost' => 'SparkPost',
            'custom' => 'Custom SMTP',
        ];

        return response()->json($providers);
    }

    /**
     * Get predefined settings for common providers.
     */
    public function getProviderSettings($provider)
    {
        $settings = [
            'gmail' => [
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
                'daily_limit' => 500,
                'hourly_limit' => 50,
            ],
            'outlook' => [
                'host' => 'smtp-mail.outlook.com',
                'port' => 587,
                'encryption' => 'tls',
                'daily_limit' => 300,
                'hourly_limit' => 30,
            ],
            'yahoo' => [
                'host' => 'smtp.mail.yahoo.com',
                'port' => 587,
                'encryption' => 'tls',
                'daily_limit' => 500,
                'hourly_limit' => 50,
            ],
            'sendgrid' => [
                'host' => 'smtp.sendgrid.net',
                'port' => 587,
                'encryption' => 'tls',
                'daily_limit' => 40000,
                'hourly_limit' => 4000,
            ],
            'mailgun' => [
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'encryption' => 'tls',
                'daily_limit' => 10000,
                'hourly_limit' => 1000,
            ],
        ];

        return response()->json($settings[$provider] ?? []);
    }

    /**
     * Debug SMTP password decryption.
     */
    public function debugPassword(SmtpConfig $smtpConfig)
    {
        try {
            $rawPassword = $smtpConfig->attributes['password'] ?? null;
            $decryptedPassword = $smtpConfig->password;
            
            \Log::info('SMTP Password Debug', [
                'smtp_id' => $smtpConfig->id,
                'raw_encrypted' => $rawPassword,
                'raw_length' => strlen($rawPassword ?? ''),
                'decrypted' => $decryptedPassword,
                'decrypted_length' => strlen($decryptedPassword ?? ''),
                'is_encrypted' => $rawPassword !== $decryptedPassword
            ]);
            
            return response()->json([
                'success' => true,
                'debug_data' => [
                    'raw_encrypted_length' => strlen($rawPassword ?? ''),
                    'decrypted_password' => $decryptedPassword,
                    'decrypted_length' => strlen($decryptedPassword ?? ''),
                    'password_preview' => substr($decryptedPassword ?? '', 0, 3) . '***',
                    'is_encrypted' => $rawPassword !== $decryptedPassword,
                    'encryption_test' => [
                        'original' => 'test123',
                        'encrypted' => encrypt('test123'),
                        'decrypted' => decrypt(encrypt('test123'))
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'raw_password' => $smtpConfig->attributes['password'] ?? 'NULL'
            ]);
        }
    }
    
    /**
     * Get active SMTP configurations for API/AJAX
     */
    public function getConfigs(Request $request)
    {
        $configs = SmtpConfig::where('is_active', true)
            ->select('id', 'name', 'from_email', 'provider')
            ->orderBy('name')
            ->get();

        return response()->json($configs);
    }
}
