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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('google_account_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // For audit
            $table->enum('type', [
                'gmail_read',
                'gmail_send', 
                'sheets_import',
                'contacts_enrichment',
                'oauth_refresh',
                'account_sync'
            ]);
            $table->enum('status', ['started', 'success', 'failed', 'partial'])->default('started');
            $table->text('message')->nullable(); // Success/error message
            $table->json('details')->nullable(); // Additional sync details
            $table->integer('items_processed')->default(0); // Emails, contacts, etc.
            $table->integer('items_created')->default(0);
            $table->integer('items_updated')->default(0);
            $table->integer('items_failed')->default(0);
            $table->timestamp('started_at')->index();
            $table->timestamp('finished_at')->nullable();
            $table->integer('duration_seconds')->nullable(); // Calculated duration
            $table->string('batch_id')->nullable(); // For grouping related operations
            $table->json('error_details')->nullable(); // Detailed error information
            $table->timestamps();

            // Indexes for performance and reporting
            $table->index(['google_account_id', 'type', 'started_at']);
            $table->index(['status', 'started_at']);
            $table->index(['type', 'status']);
            $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
