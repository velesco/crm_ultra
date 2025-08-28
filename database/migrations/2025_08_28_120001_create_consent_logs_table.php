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
        Schema::create('consent_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('cascade');
            $table->enum('consent_type', [
                'email_marketing',
                'sms_marketing',
                'whatsapp_marketing',
                'data_processing',
                'profiling',
                'third_party_sharing',
                'cookies',
                'analytics',
            ]);
            $table->enum('status', ['given', 'withdrawn', 'expired'])->default('given');
            $table->timestamp('given_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->enum('source', ['website', 'api', 'import', 'manual', 'form'])->default('website');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('legal_basis', [
                'consent',
                'contract',
                'legal_obligation',
                'vital_interests',
                'public_task',
                'legitimate_interests',
            ])->default('consent');
            $table->string('purpose')->nullable();
            $table->integer('retention_period')->nullable(); // in days
            $table->foreignId('processor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['contact_id', 'consent_type', 'status']);
            $table->index(['consent_type', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['given_at', 'withdrawn_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_logs');
    }
};
