<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerformanceMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class PerformanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

    /**
     * Display performance dashboard
     */
    public function index(Request $request)
    {
        // Get current system metrics
        $currentMetrics = $this->getCurrentMetrics();

        // Get historical metrics for charts
        $period = $request->get('period', '24h');
        $historicalMetrics = $this->getHistoricalMetrics($period);

        // Get performance alerts
        $alerts = $this->getPerformanceAlerts();

        // Calculate performance scores
        $scores = $this->calculatePerformanceScores($currentMetrics);

        // Get critical and warning metrics for alerts
        $criticalMetrics = collect();
        $warningMetrics = collect();
        $metricTypes = ['cpu', 'memory', 'disk', 'database', 'cache', 'queue'];

        // Parse current metrics for alerts
        foreach ($currentMetrics as $category => $categoryMetrics) {
            foreach ($categoryMetrics as $name => $data) {
                $mockMetric = (object) [
                    'metric_type' => $category,
                    'metric_name' => $name,
                    'value' => $data['value'],
                    'unit' => $data['unit'],
                    'formatted_value' => $data['value'] . ' ' . $data['unit'],
                    'status' => $data['status'] ?? 'normal'
                ];

                if (($data['status'] ?? 'normal') === 'critical') {
                    $criticalMetrics->push($mockMetric);
                } elseif (($data['status'] ?? 'normal') === 'warning') {
                    $warningMetrics->push($mockMetric);
                }
            }
        }

        return view('admin.performance.index', compact(
            'currentMetrics',
            'historicalMetrics',
            'alerts',
            'scores',
            'period',
            'criticalMetrics',
            'warningMetrics',
            'metricTypes'
        ));
    }

    /**
     * Show detailed performance metrics
     */
    public function show(Request $request, $metric)
    {
        $period = $request->get('period', '24h');

        switch ($metric) {
            case 'system':
                $data = $this->getSystemMetrics($period);
                break;
            case 'database':
                $data = $this->getDatabaseMetrics($period);
                break;
            case 'cache':
                $data = $this->getCacheMetrics($period);
                break;
            case 'queue':
                $data = $this->getQueueMetrics($period);
                break;
            default:
                abort(404);
        }

        return view('admin.performance.show', compact('data', 'metric', 'period'));
    }

    /**
     * Get real-time metrics (AJAX endpoint)
     */
    public function metrics(Request $request)
    {
        $metrics = $this->getCurrentMetrics();

        return response()->json([
            'success' => true,
            'data' => $metrics,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get chart data for specific metric
     */
    public function chartData(Request $request, $metric)
    {
        $period = $request->get('period', '24h');
        $data = $this->getMetricChartData($metric, $period);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Export performance report
     */
    public function export(Request $request)
    {
        $period = $request->get('period', '24h');
        $format = $request->get('format', 'csv');

        $metrics = $this->getHistoricalMetrics($period);

        if ($format === 'csv') {
            return $this->exportToCsv($metrics);
        }

        return response()->json([
            'error' => 'Unsupported export format',
        ], 400);
    }

    /**
     * Clear old performance metrics
     */
    public function cleanup(Request $request)
    {
        $days = $request->get('days', 30);

        $deleted = PerformanceMetric::where('created_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} old performance metrics",
            'deleted_count' => $deleted,
        ]);
    }

    /**
     * Get current system performance metrics
     */
    private function getCurrentMetrics()
    {
        $metrics = [];

        // System metrics
        $metrics['system'] = [
            'cpu_usage' => $this->getCpuUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'load_average' => $this->getLoadAverage(),
        ];

        // Database metrics
        $metrics['database'] = [
            'connection_count' => $this->getDatabaseConnections(),
            'query_time_avg' => $this->getAverageQueryTime(),
            'slow_queries' => $this->getSlowQueriesCount(),
            'database_size' => $this->getDatabaseSize(),
        ];

        // Cache metrics
        $metrics['cache'] = [
            'hit_rate' => $this->getCacheHitRate(),
            'memory_usage' => $this->getCacheMemoryUsage(),
            'keys_count' => $this->getCacheKeysCount(),
            'evictions' => $this->getCacheEvictions(),
        ];

        // Queue metrics
        $metrics['queue'] = [
            'pending_jobs' => $this->getPendingJobs(),
            'failed_jobs' => $this->getFailedJobs(),
            'processed_jobs' => $this->getProcessedJobs(),
            'avg_processing_time' => $this->getAvgProcessingTime(),
        ];

        // Store metrics for historical tracking
        $this->storeMetrics($metrics);

        return $metrics;
    }

    /**
     * Get historical metrics for specified period
     */
    private function getHistoricalMetrics($period)
    {
        $startDate = $this->getPeriodStartDate($period);

        return PerformanceMetric::where('created_at', '>=', $startDate)
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($metric) use ($period) {
                return $this->groupByPeriod($metric->created_at, $period);
            })
            ->map(function ($group) {
                return [
                    'timestamp' => $group->first()->created_at,
                    'cpu_usage' => $group->avg('cpu_usage'),
                    'memory_usage' => $group->avg('memory_usage'),
                    'disk_usage' => $group->avg('disk_usage'),
                    'database_connections' => $group->avg('database_connections'),
                    'cache_hit_rate' => $group->avg('cache_hit_rate'),
                    'pending_jobs' => $group->avg('pending_jobs'),
                    'failed_jobs' => $group->sum('failed_jobs'),
                ];
            });
    }

    /**
     * Get performance alerts
     */
    private function getPerformanceAlerts()
    {
        $alerts = [];
        $metrics = $this->getCurrentMetrics();

        // CPU alerts
        if ($metrics['system']['cpu_usage'] > 80) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'High CPU Usage',
                'message' => "CPU usage is at {$metrics['system']['cpu_usage']}%",
                'metric' => 'cpu_usage',
                'value' => $metrics['system']['cpu_usage'],
            ];
        }

        // Memory alerts
        if ($metrics['system']['memory_usage'] > 85) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'High Memory Usage',
                'message' => "Memory usage is at {$metrics['system']['memory_usage']}%",
                'metric' => 'memory_usage',
                'value' => $metrics['system']['memory_usage'],
            ];
        }

        // Disk alerts
        if ($metrics['system']['disk_usage'] > 90) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Low Disk Space',
                'message' => "Disk usage is at {$metrics['system']['disk_usage']}%",
                'metric' => 'disk_usage',
                'value' => $metrics['system']['disk_usage'],
            ];
        }

        // Database alerts
        if ($metrics['database']['slow_queries'] > 10) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Slow Database Queries',
                'message' => "{$metrics['database']['slow_queries']} slow queries detected",
                'metric' => 'slow_queries',
                'value' => $metrics['database']['slow_queries'],
            ];
        }

        // Cache alerts
        if ($metrics['cache']['hit_rate'] < 70) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Low Cache Hit Rate',
                'message' => "Cache hit rate is at {$metrics['cache']['hit_rate']}%",
                'metric' => 'cache_hit_rate',
                'value' => $metrics['cache']['hit_rate'],
            ];
        }

        // Queue alerts
        if ($metrics['queue']['failed_jobs'] > 5) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Failed Queue Jobs',
                'message' => "{$metrics['queue']['failed_jobs']} failed jobs need attention",
                'metric' => 'failed_jobs',
                'value' => $metrics['queue']['failed_jobs'],
            ];
        }

        if ($metrics['queue']['pending_jobs'] > 100) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Queue Backlog',
                'message' => "{$metrics['queue']['pending_jobs']} jobs pending processing",
                'metric' => 'pending_jobs',
                'value' => $metrics['queue']['pending_jobs'],
            ];
        }

        return $alerts;
    }

    /**
     * System metric methods
     */
    private function getCpuUsage()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return rand(10, 50); // Simulated for Windows
        }

        $load = sys_getloadavg();

        return round(($load[0] / 4) * 100, 2); // Assuming 4-core system
    }

    private function getMemoryUsage()
    {
        $memUsed = memory_get_usage(true);
        $memLimit = $this->parseBytes(ini_get('memory_limit'));

        return round(($memUsed / $memLimit) * 100, 2);
    }

    private function getDiskUsage()
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        $usedSpace = $totalSpace - $freeSpace;

        return round(($usedSpace / $totalSpace) * 100, 2);
    }

    private function getLoadAverage()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return [1.2, 1.1, 1.0]; // Simulated for Windows
        }

        return sys_getloadavg();
    }

    /**
     * Database metric methods
     */
    private function getDatabaseConnections()
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");

            return (int) $result[0]->Value;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getAverageQueryTime()
    {
        // Get from recent performance metrics
        return PerformanceMetric::where('created_at', '>=', now()->subHour())
            ->avg('avg_query_time') ?? 0;
    }

    private function getSlowQueriesCount()
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Slow_queries'");

            return (int) $result[0]->Value;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getDatabaseSize()
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $result = DB::select("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'size_mb'
                FROM information_schema.tables
                WHERE table_schema = ?
            ", [$dbName]);

            return $result[0]->size_mb ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Cache metric methods
     */
    private function getCacheHitRate()
    {
        if (config('cache.default') === 'redis') {
            try {
                $info = Cache::getRedis()->info();
                $hits = $info['keyspace_hits'] ?? 0;
                $misses = $info['keyspace_misses'] ?? 0;

                if ($hits + $misses === 0) {
                    return 100;
                }

                return round(($hits / ($hits + $misses)) * 100, 2);
            } catch (\Exception $e) {
                return 95; // Default good value
            }
        }

        return 95; // Default for non-Redis cache
    }

    private function getCacheMemoryUsage()
    {
        if (config('cache.default') === 'redis') {
            try {
                $info = Cache::getRedis()->info();

                return round($info['used_memory'] / (1024 * 1024), 2); // MB
            } catch (\Exception $e) {
                return 0;
            }
        }

        return 0;
    }

    private function getCacheKeysCount()
    {
        if (config('cache.default') === 'redis') {
            try {
                return Cache::getRedis()->dbSize();
            } catch (\Exception $e) {
                return 0;
            }
        }

        return 0;
    }

    private function getCacheEvictions()
    {
        if (config('cache.default') === 'redis') {
            try {
                $info = Cache::getRedis()->info();

                return $info['evicted_keys'] ?? 0;
            } catch (\Exception $e) {
                return 0;
            }
        }

        return 0;
    }

    /**
     * Queue metric methods
     */
    private function getPendingJobs()
    {
        try {
            $connection = Queue::connection();
            if (method_exists($connection, 'size')) {
                return $connection->size('default');
            }

            // Fallback for database queue
            return DB::table('jobs')->where('queue', 'default')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getFailedJobs()
    {
        return DB::table('failed_jobs')->count();
    }

    private function getProcessedJobs()
    {
        return PerformanceMetric::where('created_at', '>=', now()->subDay())
            ->sum('processed_jobs') ?? 0;
    }

    private function getAvgProcessingTime()
    {
        return PerformanceMetric::where('created_at', '>=', now()->subHour())
            ->avg('avg_processing_time') ?? 0;
    }

    /**
     * Store metrics in database
     */
    private function storeMetrics($metrics)
    {
        PerformanceMetric::create([
            'cpu_usage' => $metrics['system']['cpu_usage'],
            'memory_usage' => $metrics['system']['memory_usage'],
            'disk_usage' => $metrics['system']['disk_usage'],
            'load_average' => json_encode($metrics['system']['load_average']),
            'database_connections' => $metrics['database']['connection_count'],
            'avg_query_time' => $metrics['database']['query_time_avg'],
            'slow_queries' => $metrics['database']['slow_queries'],
            'database_size' => $metrics['database']['database_size'],
            'cache_hit_rate' => $metrics['cache']['hit_rate'],
            'cache_memory_usage' => $metrics['cache']['memory_usage'],
            'cache_keys_count' => $metrics['cache']['keys_count'],
            'cache_evictions' => $metrics['cache']['evictions'],
            'pending_jobs' => $metrics['queue']['pending_jobs'],
            'failed_jobs' => $metrics['queue']['failed_jobs'],
            'processed_jobs' => $metrics['queue']['processed_jobs'],
            'avg_processing_time' => $metrics['queue']['avg_processing_time'],
        ]);
    }

    /**
     * Helper methods
     */
    private function parseBytes($size)
    {
        $unit = strtoupper(substr($size, -1));
        $value = (int) substr($size, 0, -1);

        switch ($unit) {
            case 'G': return $value * 1024 * 1024 * 1024;
            case 'M': return $value * 1024 * 1024;
            case 'K': return $value * 1024;
            default: return $value;
        }
    }


    /**
     * Calculate performance scores
     */
    private function calculatePerformanceScores($metrics)
    {
        $scores = [
            'overall' => 100,
            'cpu' => 100,
            'memory' => 100,
            'disk' => 100,
            'database' => 100,
        ];

        // Calculate CPU score
        if (isset($metrics['cpu']['usage_percentage']['value'])) {
            $cpuUsage = $metrics['cpu']['usage_percentage']['value'];
            $scores['cpu'] = max(0, 100 - $cpuUsage);
        }

        // Calculate Memory score
        if (isset($metrics['memory']['usage_percentage']['value'])) {
            $memUsage = $metrics['memory']['usage_percentage']['value'];
            $scores['memory'] = max(0, 100 - $memUsage);
        }

        // Calculate Disk score
        if (isset($metrics['disk']['usage_percentage']['value'])) {
            $diskUsage = $metrics['disk']['usage_percentage']['value'];
            $scores['disk'] = max(0, 100 - $diskUsage);
        }

        // Calculate Database score
        if (isset($metrics['database']['query_response_time']['value'])) {
            $queryTime = $metrics['database']['query_response_time']['value'];
            $scores['database'] = max(0, 100 - ($queryTime * 2)); // Lower is better
        }

        // Calculate overall score
        $scores['overall'] = array_sum(array_slice($scores, 1)) / 4;

        return $scores;
    }

    private function getPeriodStartDate($period)
    {
        switch ($period) {
            case '1h': return now()->subHour();
            case '24h': return now()->subDay();
            case '7d': return now()->subWeek();
            case '30d': return now()->subMonth();
            default: return now()->subDay();
        }
    }

    private function groupByPeriod($date, $period)
    {
        switch ($period) {
            case '1h': return $date->format('H:i');
            case '24h': return $date->format('H:00');
            case '7d': return $date->format('M j');
            case '30d': return $date->format('M j');
            default: return $date->format('H:00');
        }
    }

    private function exportToCsv($metrics)
    {
        $filename = 'performance_metrics_'.now()->format('Y-m-d_H-i-s').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($metrics) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Timestamp', 'CPU Usage (%)', 'Memory Usage (%)', 'Disk Usage (%)',
                'DB Connections', 'Cache Hit Rate (%)', 'Pending Jobs', 'Failed Jobs',
            ]);

            foreach ($metrics as $metric) {
                fputcsv($file, [
                    $metric['timestamp'],
                    $metric['cpu_usage'],
                    $metric['memory_usage'],
                    $metric['disk_usage'],
                    $metric['database_connections'],
                    $metric['cache_hit_rate'],
                    $metric['pending_jobs'],
                    $metric['failed_jobs'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
