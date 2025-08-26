<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_sheets_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('spreadsheet_id');
            $table->string('sheet_name')->default('Sheet1');
            $table->string('range')->default('A:Z');
            $table->text('access_token'); // Encrypted
            $table->text('refresh_token'); // Encrypted
            $table->enum('sync_direction', ['import', 'export', 'bidirectional'])->default('import');
            $table->boolean('auto_sync')->default(false);
            $table->enum('sync_frequency', ['hourly', 'daily', 'weekly'])->default('daily');
            $table->timestamp('last_sync_at')->nullable();
            $table->enum('sync_status', ['active', 'inactive', 'error'])->default('active');
            $table->json('field_mapping')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['sync_status', 'auto_sync']);
            $table->index(['last_sync_at', 'sync_frequency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_sheets_integrations');
    }
};
