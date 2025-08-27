<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;
use App\Models\User;
use App\Models\EmailCampaign;
use App\Models\SmsMessage;
use App\Models\WhatsAppMessage;
use App\Models\DataImport;
use App\Models\Communication;
use App\Services\AdminService;
use Carbon\Carbon;

class AdminController extends Controller
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

    /**
     * Display the admin dashboard with system overview
     */
    public function index(Request $request)
    {
        // Get cached system stats (refreshed every 5 minutes)
        $stats = Cache::remember('admin_dashboard_stats', 300, function () {
            return $this->adminService->getSystemStats();
        });

        // Get real-time data
        $recentActivity = $this->adminService->getRecentActivity(10);
        $systemHealth = $this->adminService->getSystemHealth();
        $topUsers = $this->adminService->getTopUsersByActivity(5);
        $alertsCount = $this->adminService->getSystemAlertsCount();

        // Get chart data for dashboard
        $chartData = [
            'users_growth' => $this->adminService->getUserGrowthData(30),
            'system_usage' => $this->adminService->getSystemUsageData(7),
            'communication_trends' => $this->adminService->getCommunicationTrends(30),
            'storage_usage' => $this->adminService->getStorageUsage(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentActivity', 
            'systemHealth',
            'topUsers',
            'alertsCount',
            'chartData'
        ));
    }

    /**
     * Get system overview stats API endpoint
     */
    public function getStats(Request $request)
    {
        $stats = $this->adminService->getSystemStats();
        return response()->json($stats);
    }

    /**
     * Get system health check data
     */
    public function getHealthCheck(Request $request)
    {
        $health = $this->adminService->getSystemHealth();
        return response()->json($health);
    }

    /**
     * Get recent system activity
     */
    public function getRecentActivity(Request $request)
    {
        $limit = $request->get('limit', 20);
        $activity = $this->adminService->getRecentActivity($limit);
        return response()->json($activity);
    }

    /**
     * System maintenance mode toggle
     */
    public function toggleMaintenance(Request $request)
    {
        try {
            $isMaintenanceMode = file_exists(storage_path('framework/maintenance.php'));
            
            if ($isMaintenanceMode) {
                // Disable maintenance mode
                if (file_exists(storage_path('framework/maintenance.php'))) {
                    unlink(storage_path('framework/maintenance.php'));
                }
                $message = 'Maintenance mode disabled successfully.';
                $status = 'disabled';
            } else {
                // Enable maintenance mode
                $secret = $request->get('secret', 'admin-access-' . time());
                file_put_contents(
                    storage_path('framework/maintenance.php'),
                    "<?php return ['except' => ['{$secret}']]; ?>"
                );
                $message = 'Maintenance mode enabled successfully.';
                $status = 'enabled';
            }

            // Log maintenance action
            $this->adminService->logSystemAction(
                Auth::id(),
                'maintenance_toggle',
                "Maintenance mode {$status}",
                ['status' => $status, 'secret' => $secret ?? null]
            );

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $status,
                'secret' => $secret ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle maintenance mode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear application caches
     */
    public function clearCaches(Request $request)
    {
        try {
            // Clear various Laravel caches
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');

            // Clear custom caches
            Cache::flush();

            // Log cache clear action
            $this->adminService->logSystemAction(
                Auth::id(),
                'cache_clear',
                'Application caches cleared',
                ['timestamp' => now()]
            );

            return response()->json([
                'success' => true,
                'message' => 'All caches cleared successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear caches: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system information
     */
    public function getSystemInfo(Request $request)
    {
        $systemInfo = $this->adminService->getSystemInfo();
        return response()->json($systemInfo);
    }

    /**
     * Export system data
     */
    public function exportSystemData(Request $request)
    {
        $request->validate([
            'type' => 'required|in:users,contacts,campaigns,messages,all',
            'format' => 'in:csv,json,xlsx',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        try {
            $exportData = $this->adminService->exportSystemData(
                $request->type,
                $request->format ?? 'csv',
                $request->date_from,
                $request->date_to
            );

            // Log export action
            $this->adminService->logSystemAction(
                Auth::id(),
                'data_export',
                "System data exported: {$request->type}",
                $request->only(['type', 'format', 'date_from', 'date_to'])
            );

            return $exportData;

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system alerts and notifications
     */
    public function getSystemAlerts(Request $request)
    {
        $alerts = $this->adminService->getSystemAlerts();
        return response()->json($alerts);
    }

    /**
     * Dismiss system alert
     */
    public function dismissAlert(Request $request)
    {
        $request->validate([
            'alert_id' => 'required|string'
        ]);

        try {
            $this->adminService->dismissAlert($request->alert_id, Auth::id());
            
            return response()->json([
                'success' => true,
                'message' => 'Alert dismissed successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to dismiss alert: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * System optimization tools
     */
    public function optimize(Request $request)
    {
        $request->validate([
            'action' => 'required|in:database,storage,cache,queue,all'
        ]);

        try {
            $results = $this->adminService->optimizeSystem($request->action);

            // Log optimization action
            $this->adminService->logSystemAction(
                Auth::id(),
                'system_optimize',
                "System optimization: {$request->action}",
                ['action' => $request->action, 'results' => $results]
            );

            return response()->json([
                'success' => true,
                'message' => 'System optimization completed successfully.',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Optimization failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
