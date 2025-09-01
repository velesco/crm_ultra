<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\GoogleSheetsIntegration;
use App\Models\GoogleSheetsSyncLog;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GoogleSheetsController extends Controller
{
    protected $googleSheetsService;

    public function __construct(GoogleSheetsService $googleSheetsService)
    {
        $this->googleSheetsService = $googleSheetsService;
        $this->middleware('auth');
        $this->middleware('can:manage-integrations')->except(['index', 'show']);
    }

    /**
     * Display a listing of Google Sheets integrations
     */
    public function index()
    {
        $integrations = GoogleSheetsIntegration::withCount(['syncLogs'])
            ->with(['creator'])
            ->orderBy('sync_status', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Add statistics for each integration
        foreach ($integrations as $integration) {
            $integration->successful_syncs = $integration->syncLogs()->where('status', 'completed')->count();
            $integration->failed_syncs = $integration->syncLogs()->where('status', 'failed')->count();
            $integration->last_sync_at = $integration->syncLogs()->latest()->first()?->started_at;
            $integration->contacts_synced = $integration->syncLogs()
                ->where('status', 'completed')
                ->sum('contacts_processed');
        }

        // Overall statistics
        $stats = [
            'total_integrations' => GoogleSheetsIntegration::count(),
            'active_integrations' => GoogleSheetsIntegration::where('sync_status', 'active')->count(),
            'total_syncs' => GoogleSheetsSyncLog::count(),
            'successful_syncs' => GoogleSheetsSyncLog::where('status', 'completed')->count(),
            'failed_syncs' => GoogleSheetsSyncLog::where('status', 'failed')->count(),
            'contacts_synced_today' => GoogleSheetsSyncLog::where('status', 'completed')
                ->whereDate('started_at', today())
                ->sum('contacts_processed'),
        ];

        return view('google-sheets.index', compact('integrations', 'stats'));
    }

    /**
     * Show the form for creating a new Google Sheets integration
     */
    public function create()
    {
        // Get OAuth URL for Google Sheets authorization
        $authUrl = $this->googleSheetsService->getAuthorizationUrl();

        $syncDirections = [
            'import' => 'Import from Google Sheets to CRM',
            'export' => 'Export from CRM to Google Sheets',
            'bidirectional' => 'Bidirectional Sync',
        ];

        $syncFrequencies = [
            'manual' => 'Manual Only',
            'hourly' => 'Every Hour',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
        ];

        // Available contact fields for mapping
        $contactFields = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'whatsapp_number' => 'WhatsApp Number',
            'company' => 'Company',
            'position' => 'Position',
            'address' => 'Address',
            'city' => 'City',
            'state' => 'State',
            'country' => 'Country',
            'postal_code' => 'Postal Code',
            'website' => 'Website',
            'source' => 'Source',
            'notes' => 'Notes',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'status' => 'Status',
        ];

        return view('google-sheets.create', compact(
            'authUrl',
            'syncDirections',
            'syncFrequencies',
            'contactFields'
        ));
    }

    /**
     * Handle OAuth callback and store integration
     */
    public function oauthCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('google-sheets.create')
                ->withErrors(['oauth' => 'Google authorization was denied: '.$request->error]);
        }

        if (! $request->has('code')) {
            return redirect()->route('google-sheets.create')
                ->withErrors(['oauth' => 'No authorization code received from Google']);
        }

        try {
            // Exchange code for access token
            $tokenData = $this->googleSheetsService->exchangeCodeForToken($request->code);

            // Store token data in session for use during integration creation
            session(['google_oauth_tokens' => $tokenData]);

            return redirect()->route('google-sheets.create')
                ->with('success', 'Google authorization successful! You can now create the integration.');

        } catch (\Exception $e) {
            return redirect()->route('google-sheets.create')
                ->withErrors(['oauth' => 'Failed to authorize with Google: '.$e->getMessage()]);
        }
    }

    /**
     * Store a newly created Google Sheets integration
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'spreadsheet_id' => 'required|string',
            'sheet_name' => 'required|string|max:255',
            'sync_direction' => 'required|in:import,export,bidirectional',
            'sync_frequency' => 'required|in:manual,hourly,daily,weekly',
            'field_mapping' => 'required|array|min:1',
            'field_mapping.*' => 'required|string',
            'has_headers' => 'boolean',
            'start_row' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if OAuth tokens are available
        $tokens = session('google_oauth_tokens');
        if (! $tokens) {
            return back()->withErrors(['oauth' => 'Google authorization is required. Please authorize first.'])
                ->withInput();
        }

        try {
            // Test access to the spreadsheet
            $this->googleSheetsService->setTokens($tokens);
            $testResult = $this->googleSheetsService->testSpreadsheetAccess(
                $request->spreadsheet_id,
                $request->sheet_name
            );

            if (! $testResult['success']) {
                return back()->withErrors(['spreadsheet_id' => 'Cannot access spreadsheet: '.$testResult['error']])
                    ->withInput();
            }

            // Create integration
            $integration = GoogleSheetsIntegration::create([
                'created_by' => Auth::id(),
                'name' => $request->name,
                'spreadsheet_id' => $request->spreadsheet_id,
                'sheet_name' => $request->sheet_name,
                'sync_direction' => $request->sync_direction,
                'sync_frequency' => $request->sync_frequency,
                'field_mapping' => $request->field_mapping,
                'settings' => [
                    'has_headers' => $request->boolean('has_headers', true),
                    'start_row' => $request->start_row ?? 1,
                    'oauth_tokens' => $tokens,
                ],
                'sync_status' => 'active',
                'last_sync_at' => null,
            ]);

            // Clear tokens from session
            session()->forget('google_oauth_tokens');

            return redirect()->route('google-sheets.show', $integration)
                ->with('success', 'Google Sheets integration created successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create integration: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified Google Sheets integration
     */
    public function show(GoogleSheetsIntegration $googleSheet)
    {
        $googleSheet->load(['creator', 'syncLogs' => function ($query) {
            $query->latest()->limit(10);
        }]);

        // Statistics
        $stats = [
            'total_syncs' => $googleSheet->syncLogs()->count(),
            'successful_syncs' => $googleSheet->syncLogs()->where('status', 'completed')->count(),
            'failed_syncs' => $googleSheet->syncLogs()->where('status', 'failed')->count(),
            'contacts_processed' => $googleSheet->syncLogs()->where('status', 'completed')->sum('contacts_processed'),
            'last_sync_at' => $googleSheet->last_sync_at,
            'next_sync_at' => $this->calculateNextSyncTime($googleSheet),
            'avg_sync_time' => $googleSheet->syncLogs()->where('status', 'completed')->avg('duration_seconds'),
        ];

        // Recent sync logs
        $recentLogs = $googleSheet->syncLogs()->with(['creator'])->latest()->limit(5)->get();

        return view('google-sheets.show', compact('googleSheet', 'stats', 'recentLogs'));
    }

    /**
     * Show the form for editing the specified integration
     */
    public function edit(GoogleSheetsIntegration $googleSheet)
    {
        $syncDirections = [
            'import' => 'Import from Google Sheets to CRM',
            'export' => 'Export from CRM to Google Sheets',
            'bidirectional' => 'Bidirectional Sync',
        ];

        $syncFrequencies = [
            'manual' => 'Manual Only',
            'hourly' => 'Every Hour',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
        ];

        $contactFields = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'whatsapp_number' => 'WhatsApp Number',
            'company' => 'Company',
            'position' => 'Position',
            'address' => 'Address',
            'city' => 'City',
            'state' => 'State',
            'country' => 'Country',
            'postal_code' => 'Postal Code',
            'website' => 'Website',
            'source' => 'Source',
            'notes' => 'Notes',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'status' => 'Status',
        ];

        return view('google-sheets.edit', compact(
            'googleSheet',
            'syncDirections',
            'syncFrequencies',
            'contactFields'
        ));
    }

    /**
     * Update the specified integration
     */
    public function update(Request $request, GoogleSheetsIntegration $googleSheet)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'spreadsheet_id' => 'required|string',
            'sheet_name' => 'required|string|max:255',
            'sync_direction' => 'required|in:import,export,bidirectional',
            'sync_frequency' => 'required|in:manual,hourly,daily,weekly',
            'field_mapping' => 'required|array|min:1',
            'field_mapping.*' => 'required|string',
            'has_headers' => 'boolean',
            'start_row' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Test spreadsheet access if spreadsheet changed
            if ($googleSheet->spreadsheet_id !== $request->spreadsheet_id ||
                $googleSheet->sheet_name !== $request->sheet_name) {

                $this->googleSheetsService->setTokens($googleSheet->oauth_tokens);
                $testResult = $this->googleSheetsService->testSpreadsheetAccess(
                    $request->spreadsheet_id,
                    $request->sheet_name
                );

                if (! $testResult['success']) {
                    return back()->withErrors(['spreadsheet_id' => 'Cannot access spreadsheet: '.$testResult['error']])
                        ->withInput();
                }
            }

            $googleSheet->update([
                'name' => $request->name,
                'spreadsheet_id' => $request->spreadsheet_id,
                'sheet_name' => $request->sheet_name,
                'sync_direction' => $request->sync_direction,
                'sync_frequency' => $request->sync_frequency,
                'field_mapping' => $request->field_mapping,
                'settings' => [
                    'has_headers' => $request->boolean('has_headers', true),
                    'start_row' => $request->start_row ?? 1,
                ],
                'description' => $request->description,
            ]);

            return redirect()->route('google-sheets.show', $googleSheet)
                ->with('success', 'Google Sheets integration updated successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update integration: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified integration
     */
    public function destroy(GoogleSheetsIntegration $googleSheet)
    {
        try {
            $googleSheet->delete();

            return redirect()->route('google-sheets.index')
                ->with('success', 'Google Sheets integration deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete integration: '.$e->getMessage()]);
        }
    }

    /**
     * Toggle integration active status
     */
    public function toggleStatus(GoogleSheetsIntegration $googleSheet)
    {
        try {
            $newStatus = $googleSheet->sync_status === 'active' ? 'inactive' : 'active';
            
            $googleSheet->update([
                'sync_status' => $newStatus,
            ]);

            $status = $newStatus === 'active' ? 'activated' : 'deactivated';

            return back()->with('success', "Integration {$status} successfully.");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to toggle integration status: '.$e->getMessage()]);
        }
    }

    /**
     * Manually trigger a sync
     */
    public function sync(GoogleSheetsIntegration $googleSheet, Request $request)
    {
        if ($googleSheet->sync_status !== 'active') {
            return back()->withErrors(['error' => 'Cannot sync inactive integration.']);
        }

        $direction = $request->input('direction', $googleSheet->sync_direction);

        if (! in_array($direction, ['import', 'export', 'bidirectional'])) {
            return back()->withErrors(['error' => 'Invalid sync direction.']);
        }

        try {
            // Initialize service with integration tokens
            $this->googleSheetsService->setTokens($googleSheet->oauth_tokens);

            // Start sync process
            $result = $this->googleSheetsService->performSync($googleSheet, $direction);

            if ($result['success']) {
                return back()->with('success', "Sync completed successfully. Processed {$result['contacts_processed']} contacts in {$result['duration']} seconds.");
            } else {
                return back()->withErrors(['sync' => 'Sync failed: '.$result['error']]);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['sync' => 'Sync failed: '.$e->getMessage()]);
        }
    }

    /**
     * Test integration connection
     */
    public function test(GoogleSheetsIntegration $googleSheet)
    {
        try {
            $this->googleSheetsService->setTokens($googleSheet->oauth_tokens);

            $result = $this->googleSheetsService->testSpreadsheetAccess(
                $googleSheet->spreadsheet_id,
                $googleSheet->sheet_name
            );

            if ($result['success']) {
                return back()->with('success', 'Connection test successful. Spreadsheet is accessible.');
            } else {
                return back()->withErrors(['test' => 'Connection test failed: '.$result['error']]);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['test' => 'Connection test failed: '.$e->getMessage()]);
        }
    }

    /**
     * Refresh OAuth tokens
     */
    public function refreshTokens(GoogleSheetsIntegration $googleSheet)
    {
        try {
            $newTokens = $this->googleSheetsService->refreshTokens($googleSheet->oauth_tokens);

            $googleSheet->update([
                'oauth_tokens' => $newTokens,
            ]);

            return back()->with('success', 'OAuth tokens refreshed successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['refresh' => 'Failed to refresh tokens: '.$e->getMessage()]);
        }
    }

    /**
     * View sync logs
     */
    public function syncLogs(GoogleSheetsIntegration $googleSheet, Request $request)
    {
        $query = $googleSheet->syncLogs()->with(['creator']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('started_at', 'desc')->paginate(20);

        return view('google-sheets.sync-logs', compact('googleSheet', 'logs'));
    }

    /**
     * Preview spreadsheet data
     */
    public function preview(GoogleSheetsIntegration $googleSheet)
    {
        try {
            $this->googleSheetsService->setTokens($googleSheet->oauth_tokens);

            $preview = $this->googleSheetsService->previewSpreadsheetData(
                $googleSheet->spreadsheet_id,
                $googleSheet->sheet_name,
                10 // Preview first 10 rows
            );

            if ($preview['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $preview['data'],
                    'headers' => $preview['headers'] ?? [],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $preview['error'],
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to preview data: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate next sync time based on frequency
     */
    private function calculateNextSyncTime(GoogleSheetsIntegration $integration)
    {
        if ($integration->sync_frequency === 'manual' || ! $integration->last_sync_at) {
            return null;
        }

        $lastSync = \Carbon\Carbon::parse($integration->last_sync_at);

        switch ($integration->sync_frequency) {
            case 'hourly':
                return $lastSync->addHour();
            case 'daily':
                return $lastSync->addDay();
            case 'weekly':
                return $lastSync->addWeek();
            default:
                return null;
        }
    }
}
