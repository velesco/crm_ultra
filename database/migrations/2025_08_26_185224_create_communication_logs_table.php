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
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type', 20); // email, sms, whatsapp, system
            $table->string('direction', 10); // inbound, outbound, tracking, internal
            $table->string('channel', 20); // email, sms, whatsapp, system
            $table->text('content')->nullable();
            $table->string('subject')->nullable(); // for emails
            $table->string('phone_number', 20)->nullable(); // for SMS/WhatsApp
            $table->string('email')->nullable(); // for emails
            $table->string('status', 20)->nullable(); // sent, delivered, read, failed, etc.
            $table->string('external_id')->nullable(); // ID from external system
            $table->json('metadata')->nullable(); // Additional tracking data
            $table->decimal('cost', 10, 4)->nullable(); // Cost for SMS/calls
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['contact_id', 'type', 'created_at']);
            $table->index(['type', 'direction', 'created_at']);
            $table->index(['channel', 'status', 'created_at']);
            $table->index(['phone_number', 'type']);
            $table->index(['email', 'type']);
            $table->index('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};
