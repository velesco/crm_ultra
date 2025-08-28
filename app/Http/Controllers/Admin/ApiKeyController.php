<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ApiKeyController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

    /**
     * Display a listing of API keys
     */
    public function index(Request $request)
    {
        $query = ApiKey::with(['createdBy', 'updatedBy']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('key', 'like', "%{$search}%")
                  ->orWhereHas('createdBy', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Environment filter
        if ($request->filled('environment')) {
            $query->where('environment', $request->environment);
        }

        // Expiry filter
        if ($request->filled('expiry')) {
            switch ($request->expiry) {
                case 'expired':
                    $query->expired();
                    break;
                case 'expiring_soon':
                    $query->whereNotNull('expires_at')
                          ->whereBetween('expires_at', [now(), now()->addDays(30)]);
                    break;
                case 'never_expires':
                    $query->whereNull('expires_at');
                    break;
            }
        }

        // Usage filter
        if ($request->filled('usage')) {
            switch ($request->usage) {
                case 'unused':
                    $query->unused(30);
                    break;
                case 'active':
                    $query->usedInLastDays(30);
                    break;
                case 'high_usage':
                    $query->where('usage_count', '>', 1000);
                    break;
            }
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['name', 'status', 'environment', 'usage_count', 'last_used_at', 'expires_at', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $apiKeys = $query->paginate(15)->withQueryString();
        $statistics = ApiKey::getStatistics();

        return view('admin.api-keys.index', compact('apiKeys', 'statistics'));
    }

    /**
     * Show the form for creating a new API key
     */
    public function create()
    {
        $availablePermissions = ApiKey::getAvailablePermissions();
        $availableScopes = ApiKey::getAvailableScopes();
        
        return view('admin.api-keys.create', compact('availablePermissions', 'availableScopes'));
    }

    /**
     * Store a newly created API key
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:api_keys,name',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(ApiKey::getAvailablePermissions())),
            'scopes' => 'nullable|array',
            'scopes.*' => 'string|in:' . implode(',', array_keys(ApiKey::getAvailableScopes())),
            'environment' => 'required|in:production,staging,development',
            'allowed_ips' => 'nullable|string',
            'rate_limit_per_minute' => 'required|integer|min:1|max:1000',
            'rate_limit_per_hour' => 'required|integer|min:1|max:50000',
            'rate_limit_per_day' => 'required|integer|min:1|max:1000000',
            'expires_at' => 'nullable|date|after:now',
            'status' => 'required|in:active,inactive,suspended'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Validate IP addresses if provided
        if ($request->filled('allowed_ips')) {
            $ips = array_map('trim', explode(',', $request->allowed_ips));
            foreach ($ips as $ip) {
                if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    return redirect()->back()
                                   ->withErrors(['allowed_ips' => "Invalid IP address: {$ip}"])
                                   ->withInput();
                }
            }
        }

        try {
            DB::beginTransaction();

            $apiKey = ApiKey::create([
                'name' => $request->name,
                'description' => $request->description,
                'permissions' => $request->permissions ?? [],
                'scopes' => $request->scopes ?? [],
                'environment' => $request->environment,
                'allowed_ips' => $request->allowed_ips,
                'rate_limit_per_minute' => $request->rate_limit_per_minute,
                'rate_limit_per_hour' => $request->rate_limit_per_hour,
                'rate_limit_per_day' => $request->rate_limit_per_day,
                'expires_at' => $request->expires_at ? Carbon::parse($request->expires_at) : null,
                'status' => $request->status
            ]);

            // Log the creation
            activity()
                ->causedBy(auth()->user())
                ->performedOn($apiKey)
                ->log('API Key created: ' . $apiKey->name);

            DB::commit();

            return redirect()->route('admin.api-keys.show', $apiKey)
                           ->with('success', 'API Key created successfully!')
                           ->with('api_key', $apiKey->full_key); // Show the full key once
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                           ->withErrors(['error' => 'Failed to create API key: ' . $e->getMessage()])
                           ->withInput();
        }
    }

    /**
     * Display the specified API key
     */
    public function show(ApiKey $apiKey)
    {
        $apiKey->load(['createdBy', 'updatedBy']);
        
        // Get usage statistics for the last 30 days
        $usageStats = $this->getUsageStatistics($apiKey);
        
        return view('admin.api-keys.show', compact('apiKey', 'usageStats'));
    }

    /**
     * Show the form for editing the specified API key
     */
    public function edit(ApiKey $apiKey)
    {
        $availablePermissions = ApiKey::getAvailablePermissions();
        $availableScopes = ApiKey::getAvailableScopes();
        
        return view('admin.api-keys.edit', compact('apiKey', 'availablePermissions', 'availableScopes'));
    }

    /**
     * Update the specified API key
     */
    public function update(Request $request, ApiKey $apiKey)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('api_keys')->ignore($apiKey->id)],
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(ApiKey::getAvailablePermissions())),
            'scopes' => 'nullable|array',
            'scopes.*' => 'string|in:' . implode(',', array_keys(ApiKey::getAvailableScopes())),
            'environment' => 'required|in:production,staging,development',
            'allowed_ips' => 'nullable|string',
            'rate_limit_per_minute' => 'required|integer|min:1|max:1000',
            'rate_limit_per_hour' => 'required|integer|min:1|max:50000',
            'rate_limit_per_day' => 'required|integer|min:1|max:1000000',
            'expires_at' => 'nullable|date|after:now',
            'status' => 'required|in:active,inactive,suspended'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Validate IP addresses if provided
        if ($request->filled('allowed_ips')) {
            $ips = array_map('trim', explode(',', $request->allowed_ips));
            foreach ($ips as $ip) {
                if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    return redirect()->back()
                                   ->withErrors(['allowed_ips' => "Invalid IP address: {$ip}"])
                                   ->withInput();
                }
            }
        }

        try {
            DB::beginTransaction();

            $apiKey->update([
                'name' => $request->name,
                'description' => $request->description,
                'permissions' => $request->permissions ?? [],
                'scopes' => $request->scopes ?? [],
                'environment' => $request->environment,
                'allowed_ips' => $request->allowed_ips,
                'rate_limit_per_minute' => $request->rate_limit_per_minute,
                'rate_limit_per_hour' => $request->rate_limit_per_hour,
                'rate_limit_per_day' => $request->rate_limit_per_day,
                'expires_at' => $request->expires_at ? Carbon::parse($request->expires_at) : null,
                'status' => $request->status
            ]);

            // Log the update
            activity()
                ->causedBy(auth()->user())
                ->performedOn($apiKey)
                ->log('API Key updated: ' . $apiKey->name);

            DB::commit();

            return redirect()->route('admin.api-keys.show', $apiKey)
                           ->with('success', 'API Key updated successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                           ->withErrors(['error' => 'Failed to update API key: ' . $e->getMessage()])
                           ->withInput();
        }
    }

    /**
     * Remove the specified API key
     */
    public function destroy(ApiKey $apiKey)
    {
        try {
            // Log the deletion
            activity()
                ->causedBy(auth()->user())
                ->performedOn($apiKey)
                ->log('API Key deleted: ' . $apiKey->name);

            $apiKey->delete();

            return redirect()->route('admin.api-keys.index')
                           ->with('success', 'API Key deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Failed to delete API key: ' . $e->getMessage()]);
        }
    }

    /**
     * Regenerate API key
     */
    public function regenerate(ApiKey $apiKey)
    {
        try {
            $apiKey->regenerateKey();

            // Log the regeneration
            activity()
                ->causedBy(auth()->user())
                ->performedOn($apiKey)
                ->log('API Key regenerated: ' . $apiKey->name);

            return redirect()->route('admin.api-keys.show', $apiKey)
                           ->with('success', 'API Key regenerated successfully!')
                           ->with('api_key', $apiKey->full_key);
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Failed to regenerate API key: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle API key status
     */
    public function toggleStatus(ApiKey $apiKey)
    {
        try {
            $newStatus = $apiKey->status === 'active' ? 'inactive' : 'active';
            $apiKey->update(['status' => $newStatus]);

            // Log the status change
            activity()
                ->causedBy(auth()->user())
                ->performedOn($apiKey)
                ->log("API Key status changed to {$newStatus}: " . $apiKey->name);

            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'message' => "API Key {$newStatus} successfully!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,suspend,delete',
            'api_keys' => 'required|array|min:1',
            'api_keys.*' => 'exists:api_keys,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $apiKeys = ApiKey::whereIn('id', $request->api_keys)->get();
            $count = 0;

            foreach ($apiKeys as $apiKey) {
                switch ($request->action) {
                    case 'activate':
                        $apiKey->update(['status' => 'active']);
                        $count++;
                        break;
                    case 'deactivate':
                        $apiKey->update(['status' => 'inactive']);
                        $count++;
                        break;
                    case 'suspend':
                        $apiKey->update(['status' => 'suspended']);
                        $count++;
                        break;
                    case 'delete':
                        $apiKey->delete();
                        $count++;
                        break;
                }
            }

            // Log bulk action
            activity()
                ->causedBy(auth()->user())
                ->log("Bulk action '{$request->action}' performed on {$count} API keys");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully {$request->action}d {$count} API key(s)."
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Bulk action failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export API keys
     */
    public function export(Request $request)
    {
        try {
            $query = ApiKey::with(['createdBy']);

            // Apply same filters as index
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('environment')) {
                $query->where('environment', $request->environment);
            }

            $apiKeys = $query->get();

            $csvData = [];
            $csvData[] = [
                'Name', 'Description', 'Status', 'Environment', 'Permissions', 'Scopes',
                'Rate Limit/Min', 'Rate Limit/Hour', 'Rate Limit/Day', 'Usage Count',
                'Last Used', 'Expires At', 'Created By', 'Created At'
            ];

            foreach ($apiKeys as $apiKey) {
                $csvData[] = [
                    $apiKey->name,
                    $apiKey->description,
                    $apiKey->status,
                    $apiKey->environment,
                    implode(', ', $apiKey->permissions ?? []),
                    implode(', ', $apiKey->scopes ?? []),
                    $apiKey->rate_limit_per_minute,
                    $apiKey->rate_limit_per_hour,
                    $apiKey->rate_limit_per_day,
                    $apiKey->usage_count,
                    $apiKey->last_used_at ? $apiKey->last_used_at->format('Y-m-d H:i:s') : '',
                    $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d H:i:s') : '',
                    $apiKey->createdBy->name ?? '',
                    $apiKey->created_at->format('Y-m-d H:i:s')
                ];
            }

            $filename = 'api_keys_' . date('Y-m-d_H-i-s') . '.csv';
            $handle = fopen('php://output', 'w');

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
            exit;
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Get usage statistics for an API key
     */
    private function getUsageStatistics(ApiKey $apiKey)
    {
        // In a real application, you would have an api_key_usage_logs table
        // For now, we'll return mock data based on the usage_count
        
        return [
            'total_requests' => $apiKey->usage_count,
            'requests_today' => rand(0, min(100, $apiKey->usage_count)),
            'requests_this_week' => rand(0, min(500, $apiKey->usage_count)),
            'requests_this_month' => rand(0, $apiKey->usage_count),
            'avg_requests_per_day' => $apiKey->usage_count > 0 ? round($apiKey->usage_count / max(1, $apiKey->created_at->diffInDays(now())), 2) : 0,
            'last_7_days' => $this->generateDailyUsageChart($apiKey),
            'endpoints_usage' => $this->generateEndpointUsage($apiKey)
        ];
    }

    /**
     * Generate daily usage chart data
     */
    private function generateDailyUsageChart(ApiKey $apiKey)
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'date' => $date->format('M d'),
                'requests' => rand(0, min(50, floor($apiKey->usage_count / 7)))
            ];
        }
        return $data;
    }

    /**
     * Generate endpoint usage data
     */
    private function generateEndpointUsage(ApiKey $apiKey)
    {
        if ($apiKey->usage_count == 0) return [];

        return [
            '/api/contacts' => rand(0, floor($apiKey->usage_count * 0.4)),
            '/api/emails' => rand(0, floor($apiKey->usage_count * 0.3)),
            '/api/sms' => rand(0, floor($apiKey->usage_count * 0.2)),
            '/api/whatsapp' => rand(0, floor($apiKey->usage_count * 0.1))
        ];
    }
}
