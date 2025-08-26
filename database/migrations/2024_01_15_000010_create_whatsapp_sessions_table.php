<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_name')->unique();
            $table->string('phone_number')->nullable();
            $table->enum('status', ['disconnected', 'connecting', 'qr', 'connected', 'error'])->default('disconnected');
            $table->longText('qr_code')->nullable();
            $table->string('webhook_url')->nullable();
            $table->string('api_endpoint')->nullable();
            $table->text('api_key')->nullable(); // Encrypted
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_activity')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['is_active', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_sessions');
    }
};
