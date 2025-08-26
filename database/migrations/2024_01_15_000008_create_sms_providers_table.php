<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('provider', ['twilio', 'vonage', 'orange', 'custom'])->default('twilio');
            $table->text('api_key'); // Encrypted
            $table->text('api_secret')->nullable(); // Encrypted
            $table->string('sender_id')->nullable();
            $table->string('webhook_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('daily_limit')->nullable();
            $table->integer('sent_today')->default(0);
            $table->decimal('cost_per_sms', 8, 4)->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['is_active', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_providers');
    }
};
