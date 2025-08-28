<?php

namespace App\Http\Controllers;

use App\Models\PerformanceMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

    /**
     * Display performance dashboard
     */
    public function index()
    {
        // Get dashboard summary
        $summary = PerformanceMetric::getDashboardSummary();
        
        // Get recent critical and warning metrics
        $criticalMetrics = PerformanceMetric::critical()
            ->recent(60)
            ->latest('recorded_at')
            ->limit(10)
            ->get();
            
        $warningMetrics = PerformanceMetric::warning()
            ->recent(60)
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        // Get current system metrics
        $currentMetrics = $this->getCurrentSystemMetrics();
        
        // Get metric types for filtering
        $metricTypes = PerformanceMetric::distinct('metric_type')->pluck('metric_type');

        return view('admin.performance.index', compact(
            'summary',
            'criticalMetrics', 
            'warningMetrics',
            'currentMetrics',
            'metricTypes'
        ));
    }

    /**
     * Show detailed metrics view
     */
    public function show(Request $request)
    {
        $type = $request->get('type', 'cpu');
        $period = $request->get('period', '24h');
        
        // Parse period
        $hours = match ($period) {
            '1h' => 1,
            '6h' => 6,
            '12h' => 12,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 24,
        };

        // Get metrics for the specified type and period
        $metrics = PerformanceMetric::byType($type)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at', 'desc')
            ->paginate(50);

        // Get trends
        $trends = PerformanceMetric::getTrends($type, $hours);

        // Get statistics
        $stats = $this->getTypeStatistics($type, $hours);

        return view('admin.performance.show', compact(
            'type',
            'period',
            'metrics',
            'trends',
            'stats'
        ));
    }

    /**
     * Get real-time system metrics
     */
    public function getSystemMetrics()
    {
        $metrics = $this->getCurrentSystemMetrics();
        
        // Record metrics in database
        foreach ($metrics as $category => $categoryMetrics) {
            foreach ($categoryMetrics as $name => $data) {
                if (isset($data['value']) && isset($data['unit'])) {
                    PerformanceMetric::record(
                        $category,
                        $name,
                        $data['value'],
                        $data['unit'],
                        $data['metadata'] ?? [],
                        $data['status'] ?? 'normal'
                    );
                }
            }
        }

        return response()->json($metrics);
    }

    /**
     * Get chart data for performance metrics
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'cpu');
        $period = $request->get('period', '24h');
        
        $hours = match ($period) {
            '1h' => 1,
            '6h' => 6,
            '12h' => 12,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 24,
        };

        $trends = PerformanceMetric::getTrends($type, $hours);
        
        return response()->json([
            'labels' => array_column($trends, 'time'),
            'datasets' => [
                [
                    'label' => 'Average',
                    'data' => array_column($trends, 'average'),
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.1
                ],
                [
                    'label' => 'Maximum',
                    'data' => array_column($trends, 'max'),
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'tension' => 0.1
                ]
            ]
        ]);
    }

    /**
     * Get performance statistics
     */
    public function getStats(Request $request)
    {
        $type = $request->get('type');
        $period = $request->get('period', '24h');
        
        $hours = match ($period) {
            '1h' => 1,
            '6h' => 6,
            '12h' => 12,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 24,
        };

        if ($type) {
            return response()->json($this->getTypeStatistics($type, $hours));
        }

        // Overall statistics
        $stats = [
            'total_metrics' => PerformanceMetric::count(),
            'recent_metrics' => PerformanceMetric::recent($hours * 60)->count(),
            'critical_count' => PerformanceMetric::critical()->recent($hours * 60)->count(),
            'warning_count' => PerformanceMetric::warning()->recent($hours * 60)->count(),
            'metric_types' => PerformanceMetric::distinct('metric_type')->count(),
            'last_recorded' => PerformanceMetric::latest('recorded_at')->first()?->recorded_at?->format('Y-m-d H:i:s'),
        ];

        return response()->json($stats);
    }

    /**
     * Clean old performance metrics
     */
    public function cleanOldMetrics(Request $request)
    {
        $days = $request->get('days', 30);
        
        $deleted = PerformanceMetric::cleanOldMetrics($days);
        
        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} old performance metrics",
            'deleted_count' => $deleted
        ]);
    }

    /**
     * Export performance data
     */
    public function export(Request $request)
    {
        $type = $request->get('type');
        $period = $request->get('period', '24h');
        
        $hours = match ($period) {
            '1h' => 1,
            '6h' => 6,
            '12h' => 12,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 24,
        };

        $query = PerformanceMetric::where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at', 'desc');
            
        if ($type) {
            $query->byType($type);
        }

        $metrics = $query->get();

        $csvData = [];
        $csvData[] = ['Recorded At', 'Type', 'Name', 'Value', 'Unit', 'Status', 'Server', 'Metadata'];

        foreach ($metrics as $metric) {
            $csvData[] = [
                $metric->recorded_at->format('Y-m-d H:i:s'),
                $metric->metric_type,
                $metric->metric_name,
                $metric->value,
                $metric->unit,
                $metric->status,
                $metric->server_name ?? 'N/A',
                json_encode($metric->metadata ?? [])
            ];
        }

        $filename = 'performance_metrics_' . ($type ? $type . '_' : '') . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get current system metrics
     */
    private function getCurrentSystemMetrics(): array
    {
        $metrics = [];

        // CPU Metrics
        $metrics['cpu'] = $this->getCpuMetrics();
        
        // Memory Metrics
        $metrics['memory'] = $this->getMemoryMetrics();
        
        // Disk Metrics
        $metrics['disk'] = $this->getDiskMetrics();
        
        // Database Metrics
        $metrics['database'] = $this->getDatabaseMetrics();
        
        // Cache Metrics
        $metrics['cache'] = $this->getCacheMetrics();
        
        // Queue Metrics
        $metrics['queue'] = $this->getQueueMetrics();

        return $metrics;
    }

    /**
     * Get CPU metrics
     */
    private function getCpuMetrics(): array
    {
        $metrics = [];
        
        // Get load average (Unix/Linux only)
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $metrics['load_1min'] = [
                'value' => round($load[0], 2),
                'unit' => 'load',
                'status' => $load[0] > 2 ? 'critical' : ($load[0] > 1 ? 'warning' : 'normal'),
                'metadata' => ['description' => '1-minute load average']
            ];
        }

        // PHP process CPU time (approximate)
        $metrics['php_cpu_time'] = [
            'value' => round(microtime(true) - LARAVEL_START, 3),
            'unit' => 'seconds',
            'status' => 'normal',
            'metadata' => ['description' => 'PHP execution time']
        ];

        return $metrics;
    }

    /**
     * Get memory metrics
     */
    private function getMemoryMetrics(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->parseBytes(ini_get('memory_limit'));

        return [
            'current_usage' => [
                'value' => round($memoryUsage / 1024 / 1024, 2),
                'unit' => 'MB',
                'status' => ($memoryUsage / $memoryLimit > 0.8) ? 'critical' : (($memoryUsage / $memoryLimit > 0.6) ? 'warning' : 'normal'),
                'metadata' => ['limit_mb' => round($memoryLimit / 1024 / 1024, 2)]
            ],
            'peak_usage' => [
                'value' => round($memoryPeak / 1024 / 1024, 2),
                'unit' => 'MB',
                'status' => 'normal',
                'metadata' => ['limit_mb' => round($memoryLimit / 1024 / 1024, 2)]
            ],
            'usage_percentage' => [
                'value' => round(($memoryUsage / $memoryLimit) * 100, 2),
                'unit' => '%',
                'status' => ($memoryUsage / $memoryLimit > 0.8) ? 'critical' : (($memoryUsage / $memoryLimit > 0.6) ? 'warning' : 'normal'),
                'metadata' => ['total_limit' => round($memoryLimit / 1024 / 1024, 2) . ' MB']
            ]
        ];
    }

    /**
     * Get disk metrics
     */
    private function getDiskMetrics(): array
    {
        $rootPath = base_path();
        $totalBytes = disk_total_space($rootPath);
        $freeBytes = disk_free_space($rootPath);
        $usedBytes = $totalBytes - $freeBytes;

        return [
            'total_space' => [
                'value' => round($totalBytes / 1024 / 1024 / 1024, 2),
                'unit' => 'GB',
                'status' => 'normal',
                'metadata' => ['path' => $rootPath]
            ],
            'free_space' => [
                'value' => round($freeBytes / 1024 / 1024 / 1024, 2),
                'unit' => 'GB',
                'status' => ($freeBytes / $totalBytes < 0.1) ? 'critical' : (($freeBytes / $totalBytes < 0.2) ? 'warning' : 'normal'),
                'metadata' => ['path' => $rootPath]
            ],
            'usage_percentage' => [
                'value' => round(($usedBytes / $totalBytes) * 100, 2),
                'unit' => '%',
                'status' => (($usedBytes / $totalBytes) > 0.9) ? 'critical' : ((($usedBytes / $totalBytes) > 0.8) ? 'warning' : 'normal'),
                'metadata' => ['path' => $rootPath]
            ]
        ];
    }

    /**
     * Get database metrics
     */
    private function getDatabaseMetrics(): array
    {
        $metrics = [];

        try {
            // Connection count
            $connections = DB::select("SHOW STATUS LIKE 'Threads_connected'");
            if (!empty($connections)) {
                $metrics['connections'] = [
                    'value' => (int) $connections[0]->Value,
                    'unit' => 'count',
                    'status' => ($connections[0]->Value > 100) ? 'warning' : 'normal',
                    'metadata' => ['description' => 'Active database connections']
                ];
            }

            // Query execution time (last query)
            $startTime = microtime(true);
            DB::select('SELECT 1');
            $queryTime = (microtime(true) - $startTime) * 1000;
            
            $metrics['query_response_time'] = [
                'value' => round($queryTime, 3),
                'unit' => 'ms',
                'status' => ($queryTime > 1000) ? 'critical' : (($queryTime > 500) ? 'warning' : 'normal'),
                'metadata' => ['description' => 'Database response time']
            ];

            // Table count
            $tableCount = count(DB::select('SHOW TABLES'));
            $metrics['table_count'] = [
                'value' => $tableCount,
                'unit' => 'count',
                'status' => 'normal',
                'metadata' => ['description' => 'Number of database tables']
            ];

        } catch (\Exception $e) {
            $metrics['database_error'] = [
                'value' => 1,
                'unit' => 'error',
                'status' => 'critical',
                'metadata' => ['error' => $e->getMessage()]
            ];
        }

        return $metrics;
    }

    /**
     * Get cache metrics
     */
    private function getCacheMetrics(): array
    {
        $metrics = [];

        try {
            // Cache hit test
            $testKey = 'performance_test_' . time();
            $testValue = 'test_value';
            
            // Set cache
            $setTime = microtime(true);
            Cache::put($testKey, $testValue, 60);
            $setDuration = (microtime(true) - $setTime) * 1000;
            
            // Get cache
            $getTime = microtime(true);
            $retrieved = Cache::get($testKey);
            $getDuration = (microtime(true) - $getTime) * 1000;
            
            // Clean up
            Cache::forget($testKey);
            
            $metrics['set_response_time'] = [
                'value' => round($setDuration, 3),
                'unit' => 'ms',
                'status' => ($setDuration > 100) ? 'warning' : 'normal',
                'metadata' => ['description' => 'Cache set operation time']
            ];
            
            $metrics['get_response_time'] = [
                'value' => round($getDuration, 3),
                'unit' => 'ms',
                'status' => ($getDuration > 50) ? 'warning' : 'normal',
                'metadata' => ['description' => 'Cache get operation time']
            ];
            
            $metrics['cache_hit'] = [
                'value' => ($retrieved === $testValue) ? 1 : 0,
                'unit' => 'boolean',
                'status' => ($retrieved === $testValue) ? 'normal' : 'critical',
                'metadata' => ['description' => 'Cache functionality test']
            ];

        } catch (\Exception $e) {
            $metrics['cache_error'] = [
                'value' => 1,
                'unit' => 'error',
                'status' => 'critical',
                'metadata' => ['error' => $e->getMessage()]
            ];
        }

        return $metrics;
    }

    /**
     * Get queue metrics
     */
    private function getQueueMetrics(): array
    {
        $metrics = [];

        try {
            // Failed jobs count
            $failedJobs = DB::table('failed_jobs')->count();
            $metrics['failed_jobs'] = [
                'value' => $failedJobs,
                'unit' => 'count',
                'status' => ($failedJobs > 10) ? 'critical' : (($failedJobs > 5) ? 'warning' : 'normal'),
                'metadata' => ['description' => 'Number of failed queue jobs']
            ];

            // Recent jobs count (if jobs table exists)
            if (Schema::hasTable('jobs')) {
                $recentJobs = DB::table('jobs')
                    ->where('created_at', '>=', now()->subHour())
                    ->count();
                    
                $metrics['recent_jobs'] = [
                    'value' => $recentJobs,
                    'unit' => 'count',
                    'status' => 'normal',
                    'metadata' => ['description' => 'Jobs queued in last hour']
                ];
            }

        } catch (\Exception $e) {
            $metrics['queue_error'] = [
                'value' => 1,
                'unit' => 'error',
                'status' => 'critical',
                'metadata' => ['error' => $e->getMessage()]
            ];
        }

        return $metrics;
    }

    /**
     * Get statistics for a specific metric type
     */
    private function getTypeStatistics(string $type, int $hours): array
    {
        $metrics = PerformanceMetric::byType($type)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->get();

        if ($metrics->isEmpty()) {
            return [
                'count' => 0,
                'average' => 0,
                'min' => 0,
                'max' => 0,
                'latest' => null,
            ];
        }

        return [
            'count' => $metrics->count(),
            'average' => round($metrics->avg('value'), 4),
            'min' => $metrics->min('value'),
            'max' => $metrics->max('value'),
            'latest' => $metrics->sortByDesc('recorded_at')->first(),
            'critical_count' => $metrics->where('status', 'critical')->count(),
            'warning_count' => $metrics->where('status', 'warning')->count(),
        ];
    }

    /**
     * Parse bytes from PHP ini values
     */
    private function parseBytes(string $val): int
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int) $val;
        
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }
}
