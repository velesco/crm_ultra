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
        Schema::create('data_retention_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('data_type', [
                'contacts',
                'email_logs',
                'sms_logs',
                'whatsapp_logs',
                'system_logs',
                'login_attempts',
                'consent_logs',
                'data_requests',
                'backup_files',
                'export_files',
            ]);
            $table->integer('retention_period_days');
            $table->string('legal_basis')->nullable();
            $table->boolean('auto_delete')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('criteria')->nullable(); // Deletion criteria
            $table->json('exceptions')->nullable(); // Records to exclude
            $table->timestamp('last_executed_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['data_type', 'is_active']);
            $table->index(['is_active', 'auto_delete']);
            $table->index('last_executed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_retention_policies');
    }
};
