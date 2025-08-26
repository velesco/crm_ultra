<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_sheets_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained('google_sheets_integrations')->cascadeOnDelete();
            $table->enum('action', ['import', 'export', 'sync'])->default('sync');
            $table->enum('status', ['success', 'failed', 'partial'])->default('success');
            $table->integer('records_processed')->default(0);
            $table->integer('records_success')->default(0);
            $table->integer('records_failed')->default(0);
            $table->json('error_log')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['integration_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_sheets_sync_logs');
    }
};
