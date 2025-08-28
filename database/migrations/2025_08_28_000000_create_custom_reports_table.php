<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('general'); // general, contacts, campaigns, revenue, system
            $table->string('data_source'); // contacts, email_campaigns, sms_messages, revenues, etc.
            $table->json('columns'); // Selected columns and display settings
            $table->json('filters')->nullable(); // Query filters and conditions
            $table->json('sorting')->nullable(); // Sort order configuration
            $table->json('grouping')->nullable(); // Grouping configuration
            $table->json('aggregations')->nullable(); // Sum, count, avg, etc.
            $table->json('chart_config')->nullable(); // Chart visualization settings
            $table->enum('visibility', ['private', 'shared', 'public'])->default('private');
            $table->boolean('is_scheduled')->default(false);
            $table->json('schedule_config')->nullable(); // Schedule settings (frequency, recipients)
            $table->string('export_format')->default('table'); // table, chart, both
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->integer('run_count')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['created_by', 'visibility']);
            $table->index(['category', 'is_active']);
            $table->index(['data_source', 'is_active']);
            $table->index('last_run_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_reports');
    }
};
