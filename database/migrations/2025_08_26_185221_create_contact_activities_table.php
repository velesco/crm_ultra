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
        Schema::create('contact_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->string('activity_type', 50); // whatsapp_received, email_opened, email_clicked, sms_delivered, etc.
            $table->json('activity_data')->nullable(); // Additional data specific to the activity
            $table->string('source', 50)->nullable(); // campaign, manual, automated, etc.
            $table->string('channel', 20)->nullable(); // email, sms, whatsapp, system
            $table->timestamp('activity_timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();

            // Indexes for performance
            $table->index(['contact_id', 'activity_type']);
            $table->index(['contact_id', 'created_at']);
            $table->index(['activity_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_activities');
    }
};
