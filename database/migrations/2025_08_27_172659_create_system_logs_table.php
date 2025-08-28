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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('level')->index(); // info, warning, error, debug, critical
            $table->string('category')->index(); // authentication, email, sms, whatsapp, system, etc.
            $table->string('action'); // login, logout, send_email, create_contact, etc.
            $table->string('message');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional context data
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->string('request_id')->nullable()->index(); // For request correlation
            $table->json('context')->nullable(); // Request context (route, parameters, etc.)
            $table->timestamp('occurred_at')->index(); // When the event actually occurred
            $table->timestamps();

            // Indexes for performance
            $table->index(['level', 'occurred_at']);
            $table->index(['category', 'occurred_at']);
            $table->index(['user_id', 'occurred_at']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
