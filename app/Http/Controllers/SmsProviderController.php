<?php

namespace App\Http\Controllers;

use App\Models\SmsProvider;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SmsProviderController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
        $this->middleware('auth');
        $this->middleware('can:manage-settings')->except(['index', 'show']);
    }

    /**
     * Display a listing of SMS providers
     */
    public function index()
    {
        $providers = SmsProvider::withCount(['smsMessages'])
            ->orderBy('is_active', 'desc')
            ->orderBy('priority')
            ->get();

        // Calculate statistics for each provider
        foreach ($providers as $provider) {
            $provider->delivered_count = $provider->smsMessages()->where('status', 'delivered')->count();
            $provider->failed_count = $provider->smsMessages()->where('status', 'failed')->count();
            $provider->delivery_rate = $provider->sms_messages_count > 0 ?
                round(($provider->delivered_count / $provider->sms_messages_count) * 100, 2) : 0;
            $provider->total_cost = $provider->smsMessages()->sum('cost');
        }

        return view('sms.providers.index', compact('providers'));
    }

    /**
     * Show the form for creating a new SMS provider
     */
    public function create()
    {
        $providerTypes = [
            'twilio' => 'Twilio',
            'vonage' => 'Vonage (Nexmo)',
            'orange' => 'Orange SMS',
            'custom' => 'Custom HTTP API',
        ];

        return view('sms.providers.create', compact('providerTypes'));
    }

    /**
     * Store a newly created SMS provider
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'provider' => 'required|in:twilio,vonage,orange,custom',
            'api_key' => 'required|string|max:500',
            'api_secret' => 'nullable|string|max:500',
            'sender_id' => 'nullable|string|max:11',
            'webhook_url' => 'nullable|url',
            'daily_limit' => 'nullable|integer|min:1',
            'cost_per_sms' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'priority' => 'nullable|integer|min:1|max:100',

            // Custom provider fields
            'custom_api_url' => 'required_if:provider,custom|nullable|url',
            'custom_method' => 'required_if:provider,custom|nullable|in:GET,POST',
            'custom_headers' => 'nullable|json',
            'custom_params' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Prepare configuration based on provider type
            $config = $this->prepareProviderConfig($request);

            $provider = SmsProvider::create([
                'name' => $request->name,
                'provider' => $request->provider,
                'config' => $config,
                'sender_id' => $request->sender_id,
                'webhook_url' => $request->webhook_url,
                'daily_limit' => $request->daily_limit,
                'cost_per_sms' => $request->cost_per_sms ?? 0,
                'is_active' => $request->boolean('is_active', true),
                'priority' => $request->priority ?? 50,
            ]);

            // Test the provider connection
            $testResult = $this->testProviderConnection($provider);

            if (! $testResult['success']) {
                return back()->withErrors(['test' => 'Provider created but connection test failed: '.$testResult['error']])
                    ->with('warning', 'SMS Provider created successfully but connection test failed. Please verify your configuration.');
            }

            return redirect()->route('sms.providers.index')
                ->with('success', 'SMS Provider created and tested successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create SMS provider: '.$e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified SMS provider
     */
    public function show(SmsProvider $smsProvider)
    {
        $smsProvider->loadCount(['smsMessages']);

        // Get detailed statistics
        $stats = [
            'total_sent' => $smsProvider->smsMessages()->count(),
            'delivered' => $smsProvider->smsMessages()->where('status', 'delivered')->count(),
            'failed' => $smsProvider->smsMessages()->where('status', 'failed')->count(),
            'pending' => $smsProvider->smsMessages()->where('status', 'pending')->count(),
            'total_cost' => $smsProvider->smsMessages()->sum('cost'),
            'sent_today' => $smsProvider->smsMessages()->whereDate('created_at', today())->count(),
            'sent_this_week' => $smsProvider->smsMessages()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'sent_this_month' => $smsProvider->smsMessages()->whereMonth('created_at', now()->month)->count(),
        ];

        $stats['delivery_rate'] = $stats['total_sent'] > 0 ?
            round(($stats['delivered'] / $stats['total_sent']) * 100, 2) : 0;

        $stats['daily_usage'] = $smsProvider->smsMessages()->whereDate('created_at', today())->count();
        $stats['daily_remaining'] = $smsProvider->daily_limit ?
            max(0, $smsProvider->daily_limit - $stats['daily_usage']) : 'Unlimited';

        // Recent messages
        $recentMessages = $smsProvider->smsMessages()
            ->with(['contact'])
            ->latest()
            ->limit(10)
            ->get();

        return view('sms.providers.show', compact('smsProvider', 'stats', 'recentMessages'));
    }

    /**
     * Show the form for editing the specified SMS provider
     */
    public function edit(SmsProvider $smsProvider)
    {
        $providerTypes = [
            'twilio' => 'Twilio',
            'vonage' => 'Vonage (Nexmo)',
            'orange' => 'Orange SMS',
            'custom' => 'Custom HTTP API',
        ];

        return view('sms.providers.edit', compact('smsProvider', 'providerTypes'));
    }

    /**
     * Update the specified SMS provider
     */
    public function update(Request $request, SmsProvider $smsProvider)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'provider' => ['required', Rule::in(['twilio', 'vonage', 'orange', 'custom'])],
            'api_key' => 'required|string|max:500',
            'api_secret' => 'nullable|string|max:500',
            'sender_id' => 'nullable|string|max:11',
            'webhook_url' => 'nullable|url',
            'daily_limit' => 'nullable|integer|min:1',
            'cost_per_sms' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'priority' => 'nullable|integer|min:1|max:100',

            // Custom provider fields
            'custom_api_url' => 'required_if:provider,custom|nullable|url',
            'custom_method' => 'required_if:provider,custom|nullable|in:GET,POST',
            'custom_headers' => 'nullable|json',
            'custom_params' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Prepare configuration based on provider type
            $config = $this->prepareProviderConfig($request);

            $smsProvider->update([
                'name' => $request->name,
                'provider' => $request->provider,
                'config' => $config,
                'sender_id' => $request->sender_id,
                'webhook_url' => $request->webhook_url,
                'daily_limit' => $request->daily_limit,
                'cost_per_sms' => $request->cost_per_sms ?? 0,
                'is_active' => $request->boolean('is_active', true),
                'priority' => $request->priority ?? 50,
            ]);

            return redirect()->route('sms.providers.show', $smsProvider)
                ->with('success', 'SMS Provider updated successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update SMS provider: '.$e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified SMS provider
     */
    public function destroy(SmsProvider $smsProvider)
    {
        try {
            // Check if provider has sent messages
            if ($smsProvider->smsMessages()->count() > 0) {
                return back()->withErrors(['error' => 'Cannot delete SMS provider that has sent messages. Deactivate it instead.']);
            }

            $smsProvider->delete();

            return redirect()->route('sms.providers.index')
                ->with('success', 'SMS Provider deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete SMS provider: '.$e->getMessage()]);
        }
    }

    /**
     * Test SMS provider connection and configuration
     */
    public function test(Request $request, SmsProvider $smsProvider)
    {
        $validator = Validator::make($request->all(), [
            'test_phone' => 'required|string|max:15',
            'test_message' => 'nullable|string|max:160',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $testMessage = $request->test_message ?? 'This is a test SMS from CRM Ultra.';

            $result = $this->smsService->sendSms(
                $request->test_phone,
                $testMessage,
                null, // No contact ID for test
                $smsProvider->id
            );

            if ($result['success']) {
                return back()->with('success', 'Test SMS sent successfully! Message ID: '.($result['message_id'] ?? 'N/A'));
            } else {
                return back()->withErrors(['test' => 'Test SMS failed: '.($result['error'] ?? 'Unknown error')]);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['test' => 'Test SMS failed: '.$e->getMessage()]);
        }
    }

    /**
     * Toggle provider active status
     */
    public function toggleStatus(SmsProvider $smsProvider)
    {
        try {
            $smsProvider->update([
                'is_active' => ! $smsProvider->is_active,
            ]);

            $status = $smsProvider->is_active ? 'activated' : 'deactivated';

            return back()->with('success', "SMS Provider {$status} successfully.");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to toggle provider status: '.$e->getMessage()]);
        }
    }

    /**
     * Duplicate an existing SMS provider
     */
    public function duplicate(SmsProvider $smsProvider)
    {
        try {
            $newProvider = $smsProvider->replicate();
            $newProvider->name = $smsProvider->name.' (Copy)';
            $newProvider->is_active = false; // Start as inactive
            $newProvider->save();

            return redirect()->route('sms.providers.edit', $newProvider)
                ->with('success', 'SMS Provider duplicated successfully. Please review and activate.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to duplicate provider: '.$e->getMessage()]);
        }
    }

    /**
     * Prepare provider configuration based on type
     */
    private function prepareProviderConfig(Request $request)
    {
        $config = [
            'api_key' => $request->api_key,
            'api_secret' => $request->api_secret,
        ];

        switch ($request->provider) {
            case 'twilio':
                $config['account_sid'] = $request->api_key;
                $config['auth_token'] = $request->api_secret;
                break;

            case 'vonage':
                $config['api_key'] = $request->api_key;
                $config['api_secret'] = $request->api_secret;
                break;

            case 'orange':
                $config['username'] = $request->api_key;
                $config['password'] = $request->api_secret;
                break;

            case 'custom':
                $config['api_url'] = $request->custom_api_url;
                $config['method'] = $request->custom_method ?? 'POST';
                $config['headers'] = $request->custom_headers ? json_decode($request->custom_headers, true) : [];
                $config['params'] = $request->custom_params ? json_decode($request->custom_params, true) : [];
                break;
        }

        return $config;
    }

    /**
     * Test provider connection
     */
    private function testProviderConnection(SmsProvider $provider)
    {
        try {
            // Basic configuration validation
            if (empty($provider->config['api_key'])) {
                return ['success' => false, 'error' => 'API key is required'];
            }

            // Provider-specific validation
            switch ($provider->provider) {
                case 'twilio':
                    if (empty($provider->config['auth_token'])) {
                        return ['success' => false, 'error' => 'Auth token is required for Twilio'];
                    }
                    break;

                case 'vonage':
                    if (empty($provider->config['api_secret'])) {
                        return ['success' => false, 'error' => 'API secret is required for Vonage'];
                    }
                    break;

                case 'custom':
                    if (empty($provider->config['api_url'])) {
                        return ['success' => false, 'error' => 'API URL is required for custom provider'];
                    }
                    break;
            }

            return ['success' => true];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
