<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            
            // System metrics
            $table->decimal('cpu_usage', 5, 2)->nullable()->comment('CPU usage percentage');
            $table->decimal('memory_usage', 5, 2)->nullable()->comment('Memory usage percentage');
            $table->decimal('disk_usage', 5, 2)->nullable()->comment('Disk usage percentage');
            $table->json('load_average')->nullable()->comment('System load average (1min, 5min, 15min)');
            
            // Database metrics
            $table->integer('database_connections')->nullable()->comment('Active database connections');
            $table->decimal('avg_query_time', 8, 3)->nullable()->comment('Average query time in milliseconds');
            $table->integer('slow_queries')->nullable()->comment('Number of slow queries');
            $table->decimal('database_size', 10, 2)->nullable()->comment('Database size in MB');
            
            // Cache metrics
            $table->decimal('cache_hit_rate', 5, 2)->nullable()->comment('Cache hit rate percentage');
            $table->decimal('cache_memory_usage', 8, 2)->nullable()->comment('Cache memory usage in MB');
            $table->integer('cache_keys_count')->nullable()->comment('Number of cache keys');
            $table->integer('cache_evictions')->nullable()->comment('Number of cache evictions');
            
            // Queue metrics
            $table->integer('pending_jobs')->nullable()->comment('Number of pending jobs');
            $table->integer('failed_jobs')->nullable()->comment('Number of failed jobs');
            $table->integer('processed_jobs')->nullable()->comment('Number of processed jobs');
            $table->decimal('avg_processing_time', 8, 3)->nullable()->comment('Average job processing time in ms');
            
            // Additional fields
            $table->enum('status', ['good', 'warning', 'critical'])->default('good');
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('created_at');
            $table->index('status');
            $table->index(['created_at', 'status']);
            $table->index('cpu_usage');
            $table->index('memory_usage');
            $table->index('disk_usage');
            $table->index('failed_jobs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
};
