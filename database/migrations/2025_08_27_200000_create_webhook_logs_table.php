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
        if (! Schema::hasTable('webhook_logs')) {
            Schema::create('webhook_logs', function (Blueprint $table) {
                $table->id();
                $table->string('webhook_type')->index(); // email, sms, whatsapp, google_sheets, api, etc.
                $table->string('provider')->index(); // sendgrid, mailgun, twillio, whatsapp, etc.
                $table->string('event_type'); // delivered, bounced, opened, clicked, failed, etc.
                $table->string('status')->default('pending')->index(); // pending, processing, completed, failed, retrying
                $table->string('method', 10)->default('POST'); // HTTP method
                $table->string('url', 500); // Webhook URL that was called
                $table->json('headers')->nullable(); // Request headers
                $table->longText('payload'); // Raw webhook payload
                $table->json('processed_data')->nullable(); // Processed/normalized data
                $table->integer('attempts')->default(0); // Number of processing attempts
                $table->text('response')->nullable(); // Response sent back to webhook
                $table->integer('response_code')->nullable(); // HTTP response code
                $table->text('error_message')->nullable(); // Error details if failed
                $table->json('error_context')->nullable(); // Additional error context
                $table->string('webhook_id')->nullable()->index(); // Provider's webhook ID
                $table->string('reference_id')->nullable()->index(); // Reference to related record (email_id, sms_id, etc.)
                $table->string('reference_type')->nullable(); // Type of referenced record (email_log, sms_message, etc.)
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamp('webhook_received_at')->index(); // When webhook was received
                $table->timestamp('processed_at')->nullable(); // When webhook was processed
                $table->timestamp('next_retry_at')->nullable(); // Next retry time for failed webhooks
                $table->json('metadata')->nullable(); // Additional context data
                $table->timestamps();

                // Indexes for performance and queries
                $table->index(['webhook_type', 'created_at']);
                $table->index(['provider', 'created_at']);
                $table->index(['status', 'created_at']);
                $table->index(['event_type', 'created_at']);
                $table->index(['reference_type', 'reference_id']);
                $table->index(['next_retry_at']);
                $table->index(['attempts', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
