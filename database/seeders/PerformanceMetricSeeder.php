<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PerformanceMetric;
use Carbon\Carbon;

class PerformanceMetricSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->command->info('Seeding performance metrics...');

        // Generate sample performance metrics for the last 24 hours
        $now = Carbon::now();
        
        // CPU Metrics
        for ($i = 0; $i < 24; $i++) {
            $recordedAt = $now->copy()->subHours($i)->subMinutes(rand(0, 59));
            
            // CPU Load Average
            $cpuLoad = round(rand(50, 200) / 100, 2);
            PerformanceMetric::create([
                'metric_type' => 'cpu',
                'metric_name' => 'load_1min',
                'value' => $cpuLoad,
                'unit' => 'load',
                'metadata' => [
                    'description' => '1-minute load average',
                    'cores' => 8
                ],
                'status' => $cpuLoad > 2 ? 'critical' : ($cpuLoad > 1.5 ? 'warning' : 'normal'),
                'recorded_at' => $recordedAt,
            ]);
            
            // CPU Usage Percentage
            $cpuUsage = rand(20, 95);
            PerformanceMetric::create([
                'metric_type' => 'cpu',
                'metric_name' => 'usage_percentage',
                'value' => $cpuUsage,
                'unit' => '%',
                'metadata' => [
                    'description' => 'CPU usage percentage',
                    'cores' => 8
                ],
                'status' => $cpuUsage > 90 ? 'critical' : ($cpuUsage > 80 ? 'warning' : 'normal'),
                'recorded_at' => $recordedAt,
            ]);
        }

        // Memory Metrics
        for ($i = 0; $i < 24; $i++) {
            $recordedAt = $now->copy()->subHours($i)->subMinutes(rand(0, 59));
            
            // Memory Usage
            $memoryUsage = rand(1024, 7680); // 1GB to 7.5GB
            $memoryTotal = 8192; // 8GB total
            $memoryPercentage = round(($memoryUsage / $memoryTotal) * 100, 2);
            
            PerformanceMetric::create([
                'metric_type' => 'memory',
                'metric_name' => 'current_usage',
                'value' => round($memoryUsage / 1024, 2),
                'unit' => 'GB',
                'metadata' => [
                    'description' => 'Current memory usage',
                    'total_gb' => 8,
                    'percentage' => $memoryPercentage
                ],
                'status' => $memoryPercentage > 90 ? 'critical' : ($memoryPercentage > 80 ? 'warning' : 'normal'),
                'recorded_at' => $recordedAt,
            ]);
            
            // Memory Usage Percentage
            PerformanceMetric::create([
                'metric_type' => 'memory',
                'metric_name' => 'usage_percentage',
                'value' => $memoryPercentage,
                'unit' => '%',
                'metadata' => [
                    'description' => 'Memory usage percentage',
                    'total_gb' => 8
                ],
                'status' => $memoryPercentage > 90 ? 'critical' : ($memoryPercentage > 80 ? 'warning' : 'normal'),
                'recorded_at' => $recordedAt,
            ]);
        }

        // Disk Metrics
        for ($i = 0; $i < 24; $i++) {
            $recordedAt = $now->copy()->subHours($i)->subMinutes(rand(0, 59));
            
            // Disk Usage
            $totalDisk = 500; // 500GB
            $usedDisk = rand(100, 450); // 100GB to 450GB
            $diskPercentage = round(($usedDisk / $totalDisk) * 100, 2);
            
            PerformanceMetric::create([
                'metric_type' => 'disk',
                'metric_name' => 'usage_percentage',
                'value' => $diskPercentage,
                'unit' => '%',
                'metadata' => [
                    'description' => 'Disk usage percentage',
                    'total_gb' => $totalDisk,
                    'used_gb' => $usedDisk,
                    'free_gb' => $totalDisk - $usedDisk,
                    'path' => '/'
                ],
                'status' => $diskPercentage > 90 ? 'critical' : ($diskPercentage > 85 ? 'warning' : 'normal'),
                'recorded_at' => $recordedAt,
            ]);
            
            // Free Space
            PerformanceMetric::create([
                'metric_type' => 'disk',
                'metric_name' => 'free_space',
                'value' => $totalDisk - $usedDisk,
                'unit' => 'GB',
                'metadata' => [
                    'description' => 'Available disk space',
                    'total_gb' => $totalDisk,
                    'path' => '/'
                ],
                'status' => (($totalDisk - $usedDisk) / $totalDisk) < 0.1 ? 'critical' : ((($totalDisk - $usedDisk) / $totalDisk) < 0.2 ? 'warning' : 'normal'),
                'recorded_at' => $recordedAt,
            ]);
        }

        // Database Metrics
        for ($i = 0; $i < 24; $i++) {
            $recordedAt = $now->copy()->subHours($i)->subMinutes(rand(0, 59));
            
            // Query Response Time
            $responseTime = rand(1, 500); // 1ms to 500ms
            PerformanceMetric::create([
                'metric_type' => 'database',
                'metric_name' => 'query_response_time',
                'value' => $responseTime,
                'unit' => 'ms',
                'metadata' => [
                    'description' => 'Average database query response time',
                    'connection_pool_size' => 10
                ],
                'status' => $responseTime > 1000 ? 'critical' : ($responseTime > 500 ? 'warning' : 'normal'),
                'recorded_at' => $recordedAt,
            ]);
            
            // Active Connections
            $connections = rand(1, 50);
            PerformanceMetric::create([
                'metric_type' => 'database',
                'metric_name' => 'active_connections',
                'value' => $connections,
                'unit' => 'count',
                'metadata' => [
                    'description' => 'Active database connections',
                    'max_connections' => 100
                ],
                'status' => $connections > 80 ? 'critical' : ($connections > 60 ? 'warning' : 'normal'),
                'recorded_at' => $recordedAt,
            ]);
        }

        // Cache Metrics
        for ($i = 0; $i < 24; $i++) {
            $recordedAt = $now->copy()->subHours($i)->subMinutes(rand(0, 59));
            
            // Cache Hit Rate
            $hitRate = rand(85, 99);
            PerformanceMetric::create([
                'metric_type' => 'cache',
                'metric_name' => 'hit_rate',
                'value' => $hitRate,
                'unit' => '%',
                'metadata' => [
                    'description' => 'Cache hit rate percentage',
                    'provider' => 'Redis'
                ],
                'status' => $hitRate < 80 ? 'critical' : ($hitRate < 90 ? 'warning' : 'normal'),
                'recorded_at' => $recordedAt,
            ]);
            
            // Cache Response Time
            $cacheResponseTime = rand(1, 50); // 1ms to 50ms
            PerformanceMetric::create([
                'metric_type' => 'cache',
                'metric_name' => 'response_time',
                'value' => $cacheResponseTime,
                'unit' => 'ms',
                'metadata' => [
                    'description' => 'Cache operation response time',
                    'provider' => 'Redis'
                ],
                'status' => $cacheResponseTime > 100 ? 'critical' : ($cacheResponseTime > 50 ? 'warning' : 'normal'),
                'recorded_at' => $recordedAt,
            ]);
        }

        // Queue Metrics
        for ($i = 0; $i < 24; $i++) {
            $recordedAt = $now->copy()->subHours($i)->subMinutes(rand(0, 59));
            
            // Failed Jobs
            $failedJobs = rand(0, 10);
            PerformanceMetric::create([
                'metric_type' => 'queue',
                'metric_name' => 'failed_jobs',
                'value' => $failedJobs,
                'unit' => 'count',
                'metadata' => [
                    'description' => 'Number of failed queue jobs',
                    'queue_driver' => 'Redis'
                ],
                'status' => $failedJobs > 10 ? 'critical' : ($failedJobs > 5 ? 'warning' : 'normal'),
                'recorded_at' => $recordedAt,
            ]);
            
            // Pending Jobs
            $pendingJobs = rand(0, 100);
            PerformanceMetric::create([
                'metric_type' => 'queue',
                'metric_name' => 'pending_jobs',
                'value' => $pendingJobs,
                'unit' => 'count',
                'metadata' => [
                    'description' => 'Number of pending queue jobs',
                    'queue_driver' => 'Redis'
                ],
                'status' => $pendingJobs > 500 ? 'critical' : ($pendingJobs > 200 ? 'warning' : 'normal'),
                'recorded_at' => $recordedAt,
            ]);
        }

        $this->command->info('Performance metrics seeded successfully!');
        $this->command->info('Total metrics created: ' . PerformanceMetric::count());
    }
}
