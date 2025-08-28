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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key', 64)->unique();
            $table->string('prefix', 10)->default('ak');
            $table->text('description')->nullable();
            $table->json('permissions')->nullable(); // Array of allowed permissions
            $table->json('scopes')->nullable(); // Array of allowed scopes (contacts, emails, sms, whatsapp, etc.)
            $table->string('environment')->default('production'); // production, staging, development
            $table->ipAddress('allowed_ips')->nullable(); // Comma separated IPs
            $table->integer('rate_limit_per_minute')->default(60);
            $table->integer('rate_limit_per_hour')->default(1000);
            $table->integer('rate_limit_per_day')->default(10000);
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);
            $table->string('status')->default('active'); // active, inactive, suspended
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->json('metadata')->nullable(); // Additional configuration
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'expires_at']);
            $table->index(['created_by']);
            $table->index(['last_used_at']);
            $table->index(['environment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
