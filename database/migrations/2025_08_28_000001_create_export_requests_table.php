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
        Schema::create('export_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('data_type', [
                'contacts',
                'email_campaigns',
                'sms_messages',
                'whatsapp_messages',
                'revenue',
                'communications',
                'system_logs',
                'custom',
            ]);
            $table->enum('format', ['csv', 'xlsx', 'json', 'pdf'])->default('csv');
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->longText('custom_query')->nullable();

            // Status and processing
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->integer('progress')->default(0);
            $table->text('status_message')->nullable();
            $table->text('error_message')->nullable();

            // Scheduling
            $table->timestamp('scheduled_for')->nullable();
            $table->enum('recurring_frequency', ['daily', 'weekly', 'monthly'])->nullable();

            // Settings
            $table->boolean('is_public')->default(false);
            $table->boolean('notify_on_completion')->default(true);
            $table->boolean('include_attachments')->default(false);

            // Timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('download_count')->default(0);

            // User relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('data_type');
            $table->index('format');
            $table->index('scheduled_for');
            $table->index('user_id');
            $table->index('created_by');
            $table->index('created_at');
            $table->index(['status', 'scheduled_for']);
            $table->index(['user_id', 'status']);
            $table->index(['data_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_requests');
    }
};
