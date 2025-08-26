<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('whatsapp_sessions')->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts');
            $table->string('message_id')->nullable();
            $table->string('from_number');
            $table->string('to_number');
            $table->enum('message_type', ['text', 'image', 'audio', 'video', 'document', 'location', 'contact'])->default('text');
            $table->longText('content');
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'direction', 'created_at']);
            $table->index(['contact_id', 'created_at']);
            $table->index(['status', 'direction']);
            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
