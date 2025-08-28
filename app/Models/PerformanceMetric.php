<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class PerformanceMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'cpu_usage',
        'memory_usage',
        'disk_usage',
        'load_average',
        'database_connections',
        'avg_query_time',
        'slow_queries',
        'database_size',
        'cache_hit_rate',
        'cache_memory_usage',
        'cache_keys_count',
        'cache_evictions',
        'pending_jobs',
        'failed_jobs',
        'processed_jobs',
        'avg_processing_time',
        'status',
        'notes'
    ];

    protected $casts = [
        'cpu_usage' => 'decimal:2',
        'memory_usage' => 'decimal:2',
        'disk_usage' => 'decimal:2',
        'load_average' => 'json',
        'database_connections' => 'integer',
        'avg_query_time' => 'decimal:3',
        'slow_queries' => 'integer',
        'database_size' => 'decimal:2',
        'cache_hit_rate' => 'decimal:2',
        'cache_memory_usage' => 'decimal:2',
        'cache_keys_count' => 'integer',
        'cache_evictions' => 'integer',
        'pending_jobs' => 'integer',
        'failed_jobs' => 'integer',
        'processed_jobs' => 'integer',
        'avg_processing_time' => 'decimal:3',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // ===========================
    // SCOPES
    // ===========================

    /**
     * Scope by date range
     */
    public function scopeByDateRange(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for recent metrics
     */
    public function scopeRecent(Builder $query, int $minutes = 60): Builder
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope for today's metrics
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for this week's metrics
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope for this month's metrics
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    /**
     * Scope for critical performance
     */
    public function scopeCritical(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('cpu_usage', '>', 90)
              ->orWhere('memory_usage', '>', 90)
              ->orWhere('disk_usage', '>', 95)
              ->orWhere('failed_jobs', '>', 10);
        });
    }

    /**
     * Scope for warning performance
     */
    public function scopeWarning(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereBetween('cpu_usage', [70, 90])
              ->orWhereBetween('memory_usage', [80, 90])
              ->orWhereBetween('disk_usage', [85, 95])
              ->orWhereBetween('failed_jobs', [5, 10])
              ->orWhere('cache_hit_rate', '<', 70);
        });
    }

    /**
     * Scope for good performance
     */
    public function scopeGood(Builder $query): Builder
    {
        return $query->where('cpu_usage', '<', 70)
                    ->where('memory_usage', '<', 80)
                    ->where('disk_usage', '<', 85)
                    ->where('failed_jobs', '<=', 5)
                    ->where('cache_hit_rate', '>=', 70);
    }

    // ===========================
    // HELPER METHODS
    // ===========================

    /**
     * Get overall health status
     */
    public function getHealthStatusAttribute(): string
    {
        if ($this->cpu_usage > 90 || $this->memory_usage > 90 || 
            $this->disk_usage > 95 || $this->failed_jobs > 10) {
            return 'critical';
        }
        
        if ($this->cpu_usage > 70 || $this->memory_usage > 80 || 
            $this->disk_usage > 85 || $this->failed_jobs > 5 || 
            $this->cache_hit_rate < 70) {
            return 'warning';
        }
        
        return 'good';
    }

    /**
     * Get health status badge class
     */
    public function getHealthBadgeClassAttribute(): string
    {
        return match ($this->health_status) {
            'good' => 'badge bg-success',
            'warning' => 'badge bg-warning',
            'critical' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get formatted CPU usage
     */
    public function getFormattedCpuUsageAttribute(): string
    {
        return number_format($this->cpu_usage, 1) . '%';
    }

    /**
     * Get formatted memory usage
     */
    public function getFormattedMemoryUsageAttribute(): string
    {
        return number_format($this->memory_usage, 1) . '%';
    }

    /**
     * Get formatted disk usage
     */
    public function getFormattedDiskUsageAttribute(): string
    {
        return number_format($this->disk_usage, 1) . '%';
    }

    /**
     * Get formatted cache hit rate
     */
    public function getFormattedCacheHitRateAttribute(): string
    {
        return number_format($this->cache_hit_rate, 1) . '%';
    }

    /**
     * Get formatted database size
     */
    public function getFormattedDatabaseSizeAttribute(): string
    {
        return number_format($this->database_size, 1) . ' MB';
    }

    /**
     * Get formatted cache memory usage
     */
    public function getFormattedCacheMemoryAttribute(): string
    {
        return number_format($this->cache_memory_usage, 1) . ' MB';
    }

    /**
     * Get formatted average query time
     */
    public function getFormattedQueryTimeAttribute(): string
    {
        return number_format($this->avg_query_time, 3) . ' ms';
    }

    /**
     * Get formatted average processing time
     */
    public function getFormattedProcessingTimeAttribute(): string
    {
        return number_format($this->avg_processing_time, 3) . ' ms';
    }

    /**
     * Get load average as string
     */
    public function getFormattedLoadAverageAttribute(): string
    {
        $loads = is_array($this->load_average) ? $this->load_average : [];
        return implode(', ', array_map(fn($load) => number_format($load, 2), $loads));
    }

    // ===========================
    // STATIC METHODS
    // ===========================

    /**
     * Get latest metrics
     */
    public static function getLatest(): ?self
    {
        return self::latest('created_at')->first();
    }

    /**
     * Get metrics summary for dashboard
     */
    public static function getDashboardSummary(): array
    {
        $latest = self::getLatest();
        $todayCount = self::today()->count();
        $criticalCount = self::recent(60)->critical()->count();
        $warningCount = self::recent(60)->warning()->count();
        
        return [
            'total_metrics' => self::count(),
            'today_metrics' => $todayCount,
            'critical_count' => $criticalCount,
            'warning_count' => $warningCount,
            'good_count' => self::recent(60)->good()->count(),
            'last_recorded' => $latest?->created_at,
            'health_status' => $latest?->health_status ?? 'unknown'
        ];
    }

    /**
     * Get performance trends for specific metric
     */
    public static function getTrends(string $metric, int $hours = 24): array
    {
        $metrics = self::where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at')
            ->get();

        $trends = [];
        $groupedMetrics = $metrics->groupBy(function ($item) use ($hours) {
            // Group by hour for 24h, by 10min for shorter periods
            $format = $hours > 6 ? 'H:00' : 'H:i';
            return $item->created_at->format($format);
        });

        foreach ($groupedMetrics as $time => $timeMetrics) {
            $trends[] = [
                'time' => $time,
                'value' => round($timeMetrics->avg($metric), 2),
                'max' => round($timeMetrics->max($metric), 2),
                'min' => round($timeMetrics->min($metric), 2),
                'count' => $timeMetrics->count(),
            ];
        }

        return $trends;
    }

    /**
     * Get system health score (0-100)
     */
    public static function getHealthScore(): int
    {
        $latest = self::getLatest();
        if (!$latest) return 0;

        $score = 100;
        
        // Deduct points for high usage
        $score -= min($latest->cpu_usage, 100);
        $score -= min($latest->memory_usage, 100);
        $score -= min($latest->disk_usage / 2, 50); // Disk is less critical
        
        // Deduct for failed jobs
        $score -= min($latest->failed_jobs * 2, 20);
        
        // Deduct for low cache hit rate
        if ($latest->cache_hit_rate < 80) {
            $score -= (80 - $latest->cache_hit_rate);
        }
        
        return max($score, 0);
    }

    /**
     * Clean old metrics
     */
    public static function cleanOldMetrics(int $daysToKeep = 30): int
    {
        return self::where('created_at', '<', now()->subDays($daysToKeep))->delete();
    }

    /**
     * Get alerts based on current metrics
     */
    public static function getAlerts(): array
    {
        $latest = self::getLatest();
        if (!$latest) return [];

        $alerts = [];

        // CPU alerts
        if ($latest->cpu_usage > 90) {
            $alerts[] = [
                'type' => 'danger',
                'metric' => 'CPU Usage',
                'value' => $latest->formatted_cpu_usage,
                'message' => 'CPU usage is critically high'
            ];
        } elseif ($latest->cpu_usage > 70) {
            $alerts[] = [
                'type' => 'warning',
                'metric' => 'CPU Usage',
                'value' => $latest->formatted_cpu_usage,
                'message' => 'CPU usage is elevated'
            ];
        }

        // Memory alerts
        if ($latest->memory_usage > 90) {
            $alerts[] = [
                'type' => 'danger',
                'metric' => 'Memory Usage',
                'value' => $latest->formatted_memory_usage,
                'message' => 'Memory usage is critically high'
            ];
        } elseif ($latest->memory_usage > 80) {
            $alerts[] = [
                'type' => 'warning',
                'metric' => 'Memory Usage',
                'value' => $latest->formatted_memory_usage,
                'message' => 'Memory usage is high'
            ];
        }

        // Disk alerts
        if ($latest->disk_usage > 95) {
            $alerts[] = [
                'type' => 'danger',
                'metric' => 'Disk Usage',
                'value' => $latest->formatted_disk_usage,
                'message' => 'Disk space is critically low'
            ];
        } elseif ($latest->disk_usage > 85) {
            $alerts[] = [
                'type' => 'warning',
                'metric' => 'Disk Usage',
                'value' => $latest->formatted_disk_usage,
                'message' => 'Disk space is running low'
            ];
        }

        // Queue alerts
        if ($latest->failed_jobs > 10) {
            $alerts[] = [
                'type' => 'danger',
                'metric' => 'Failed Jobs',
                'value' => $latest->failed_jobs,
                'message' => 'Too many failed jobs need attention'
            ];
        } elseif ($latest->failed_jobs > 5) {
            $alerts[] = [
                'type' => 'warning',
                'metric' => 'Failed Jobs',
                'value' => $latest->failed_jobs,
                'message' => 'Some failed jobs need review'
            ];
        }

        // Cache alerts
        if ($latest->cache_hit_rate < 50) {
            $alerts[] = [
                'type' => 'danger',
                'metric' => 'Cache Hit Rate',
                'value' => $latest->formatted_cache_hit_rate,
                'message' => 'Cache hit rate is critically low'
            ];
        } elseif ($latest->cache_hit_rate < 70) {
            $alerts[] = [
                'type' => 'warning',
                'metric' => 'Cache Hit Rate',
                'value' => $latest->formatted_cache_hit_rate,
                'message' => 'Cache hit rate could be improved'
            ];
        }

        return $alerts;
    }
}
