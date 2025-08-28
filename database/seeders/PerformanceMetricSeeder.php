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
        
        for ($i = 0; $i < 24; $i++) {
            $recordedAt = $now->copy()->subHours($i)->subMinutes(rand(0, 59));
            
            // Create comprehensive performance metrics
            PerformanceMetric::create([
                'cpu_usage' => rand(20, 95),
                'memory_usage' => rand(30, 80),
                'disk_usage' => rand(40, 90),
                'load_average' => json_encode([
                    'load_1min' => round(rand(50, 200) / 100, 2),
                    'load_5min' => round(rand(60, 180) / 100, 2),
                    'load_15min' => round(rand(70, 160) / 100, 2)
                ]),
                'database_connections' => rand(5, 50),
                'avg_query_time' => round(rand(1, 100) / 10, 2),
                'slow_queries' => rand(0, 10),
                'database_size' => round(rand(500, 2000) / 10, 2),
                'cache_hit_rate' => rand(80, 99),
                'cache_memory_usage' => round(rand(50, 200) / 10, 2),
                'cache_keys_count' => rand(100, 1000),
                'cache_evictions' => rand(0, 50),
                'pending_jobs' => rand(0, 100),
                'failed_jobs' => rand(0, 5),
                'processed_jobs' => rand(50, 500),
                'avg_processing_time' => round(rand(10, 1000) / 10, 2),
                'status' => ['good', 'warning', 'critical'][rand(0, 2)],
                'notes' => $i % 5 == 0 ? 'Scheduled maintenance window' : null,
                'created_at' => $recordedAt,
                'updated_at' => $recordedAt
            ]);
        }
        
        $this->command->info('Created 24 hours of performance metrics data (24 total records)');
    }
}