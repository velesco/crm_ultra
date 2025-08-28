<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SystemLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

    /**
     * Display system logs with filtering and statistics
     */
    public function index(Request $request)
    {
        $query = SystemLog::with('user')->orderBy('occurred_at', 'desc');

        // Apply filters
        if ($request->filled('level')) {
            $query->level($request->level);
        }

        if ($request->filled('category')) {
            $query->category($request->category);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $logs = $query->paginate(50);

        // Get statistics
        $statistics = $this->getStatistics();

        // Get filter options
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $levels = [
            SystemLog::LEVEL_DEBUG => 'Debug',
            SystemLog::LEVEL_INFO => 'Info',
            SystemLog::LEVEL_WARNING => 'Warning',
            SystemLog::LEVEL_ERROR => 'Error',
            SystemLog::LEVEL_CRITICAL => 'Critical',
        ];
        $categories = [
            SystemLog::CATEGORY_AUTHENTICATION => 'Authentication',
            SystemLog::CATEGORY_EMAIL => 'Email',
            SystemLog::CATEGORY_SMS => 'SMS',
            SystemLog::CATEGORY_WHATSAPP => 'WhatsApp',
            SystemLog::CATEGORY_SYSTEM => 'System',
            SystemLog::CATEGORY_CONTACT => 'Contact',
            SystemLog::CATEGORY_CAMPAIGN => 'Campaign',
            SystemLog::CATEGORY_API => 'API',
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.system-logs.table', compact('logs'))->render(),
                'statistics' => $statistics,
            ]);
        }

        return view('admin.system-logs.index', compact(
            'logs',
            'statistics',
            'users',
            'levels',
            'categories'
        ));
    }

    /**
     * Show detailed log view
     */
    public function show(SystemLog $systemLog)
    {
        $systemLog->load('user');
        
        // Get related logs (same request_id or user within 5 minutes)
        $relatedLogs = collect();
        
        if ($systemLog->request_id) {
            $relatedLogs = SystemLog::where('request_id', $systemLog->request_id)
                ->where('id', '!=', $systemLog->id)
                ->with('user')
                ->orderBy('occurred_at')
                ->get();
        } else {
            $relatedLogs = SystemLog::where('user_id', $systemLog->user_id)
                ->whereBetween('occurred_at', [
                    $systemLog->occurred_at->subMinutes(5),
                    $systemLog->occurred_at->addMinutes(5)
                ])
                ->where('id', '!=', $systemLog->id)
                ->with('user')
                ->orderBy('occurred_at')
                ->get();
        }

        return view('admin.system-logs.show', compact('systemLog', 'relatedLogs'));
    }

    /**
     * Get chart data for analytics
     */
    public function chartData(Request $request)
    {
        $period = $request->get('period', '7d'); // 24h, 7d, 30d, 90d

        $cacheKey = "system_logs_chart_data_{$period}";
        
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($period) {
            $startDate = match($period) {
                '24h' => Carbon::now()->subDay(),
                '7d' => Carbon::now()->subDays(7),
                '30d' => Carbon::now()->subDays(30),
                '90d' => Carbon::now()->subDays(90),
                default => Carbon::now()->subDays(7)
            };

            $groupBy = match($period) {
                '24h' => 'hour',
                '7d' => 'day',
                '30d' => 'day',
                '90d' => 'week',
                default => 'day'
            };

            $format = match($period) {
                '24h' => 'H:00',
                '7d' => 'M-d',
                '30d' => 'M-d',
                '90d' => 'W',
                default => 'M-d'
            };

            // Activity trends
            $activityData = SystemLog::where('occurred_at', '>=', $startDate)
                ->selectRaw("
                    DATE_FORMAT(occurred_at, '%{$format}') as period,
                    COUNT(*) as total,
                    SUM(CASE WHEN level = 'error' THEN 1 ELSE 0 END) as errors,
                    SUM(CASE WHEN level = 'warning' THEN 1 ELSE 0 END) as warnings,
                    SUM(CASE WHEN level = 'info' THEN 1 ELSE 0 END) as info
                ")
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            // Category distribution
            $categoryData = SystemLog::where('occurred_at', '>=', $startDate)
                ->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->orderByDesc('count')
                ->get();

            // Level distribution
            $levelData = SystemLog::where('occurred_at', '>=', $startDate)
                ->selectRaw('level, COUNT(*) as count')
                ->groupBy('level')
                ->get();

            return [
                'activity' => $activityData,
                'categories' => $categoryData,
                'levels' => $levelData,
            ];
        });
    }

    /**
     * Get health metrics
     */
    public function healthMetrics()
    {
        $cacheKey = 'system_logs_health_metrics';
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $last24h = Carbon::now()->subDay();
            $last7d = Carbon::now()->subDays(7);

            return [
                'error_rate_24h' => $this->calculateErrorRate($last24h),
                'error_rate_7d' => $this->calculateErrorRate($last7d),
                'active_users_24h' => SystemLog::where('occurred_at', '>=', $last24h)
                    ->distinct('user_id')->count('user_id'),
                'top_errors' => SystemLog::errors()
                    ->where('occurred_at', '>=', $last24h)
                    ->selectRaw('message, COUNT(*) as count')
                    ->groupBy('message')
                    ->orderByDesc('count')
                    ->limit(5)
                    ->get(),
                'system_alerts' => $this->getSystemAlerts(),
            ];
        });
    }

    /**
     * Get recent activity
     */
    public function recentActivity()
    {
        return SystemLog::with('user')
            ->orderBy('occurred_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'message' => $log->message,
                    'level' => $log->level,
                    'category' => $log->category,
                    'user' => $log->user ? $log->user->name : 'System',
                    'occurred_at' => $log->occurred_at->diffForHumans(),
                    'badge_class' => $log->level_badge_class,
                    'icon' => $log->category_icon,
                ];
            });
    }

    /**
     * Export logs to CSV
     */
    public function export(Request $request)
    {
        $query = SystemLog::with('user')->orderBy('occurred_at', 'desc');

        // Apply same filters as index
        if ($request->filled('level')) {
            $query->level($request->level);
        }

        if ($request->filled('category')) {
            $query->category($request->category);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $logs = $query->limit(10000)->get(); // Limit to prevent memory issues

        $csvData = "ID,Date,Level,Category,Action,Message,User,IP Address\n";
        
        foreach ($logs as $log) {
            $csvData .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $log->id,
                $log->occurred_at->format('Y-m-d H:i:s'),
                $log->level,
                $log->category,
                $log->action,
                str_replace('"', '""', $log->message),
                $log->user ? $log->user->name : 'System',
                $log->ip_address ?? 'N/A'
            );
        }

        $filename = 'system-logs-' . date('Y-m-d-H-i-s') . '.csv';

        return Response::make($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Clear old logs
     */
    public function clearOld(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'levels' => 'array',
            'levels.*' => 'in:debug,info,warning,error,critical',
        ]);

        $cutoffDate = Carbon::now()->subDays($request->days);
        $query = SystemLog::where('occurred_at', '<', $cutoffDate);

        if ($request->filled('levels')) {
            $query->whereIn('level', $request->levels);
        }

        $deletedCount = $query->count();
        $query->delete();

        // Log this action
        SystemLog::info(
            SystemLog::CATEGORY_SYSTEM,
            'clear_old_logs',
            "Cleared {$deletedCount} old log entries older than {$request->days} days",
            [
                'deleted_count' => $deletedCount,
                'cutoff_days' => $request->days,
                'levels_filter' => $request->levels,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Successfully cleared {$deletedCount} old log entries.",
            'deleted_count' => $deletedCount,
        ]);
    }

    /**
     * Get system statistics
     */
    private function getStatistics()
    {
        $cacheKey = 'system_logs_statistics';
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $stats = SystemLog::getStatistics();
            
            $recentErrors = SystemLog::errors()
                ->where('occurred_at', '>=', Carbon::now()->subHour())
                ->count();
                
            $todayActivity = SystemLog::whereDate('occurred_at', today())
                ->selectRaw('level, COUNT(*) as count')
                ->groupBy('level')
                ->pluck('count', 'level')
                ->toArray();

            return array_merge($stats, [
                'recent_errors' => $recentErrors,
                'today_activity' => $todayActivity,
            ]);
        });
    }

    /**
     * Calculate error rate for a given period
     */
    private function calculateErrorRate($since)
    {
        $totalLogs = SystemLog::where('occurred_at', '>=', $since)->count();
        $errorLogs = SystemLog::errors()->where('occurred_at', '>=', $since)->count();
        
        return $totalLogs > 0 ? round(($errorLogs / $totalLogs) * 100, 2) : 0;
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts()
    {
        $alerts = [];
        
        // High error rate alert
        $errorRate = $this->calculateErrorRate(Carbon::now()->subHour());
        if ($errorRate > 10) {
            $alerts[] = [
                'type' => 'danger',
                'message' => "High error rate detected: {$errorRate}% in the last hour",
                'action' => 'Check recent error logs',
            ];
        }
        
        // Large number of failed logins
        $failedLogins = SystemLog::where('action', 'failed_login')
            ->where('occurred_at', '>=', Carbon::now()->subHour())
            ->count();
            
        if ($failedLogins > 10) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "High number of failed login attempts: {$failedLogins} in the last hour",
                'action' => 'Review authentication logs',
            ];
        }
        
        return $alerts;
    }
}
