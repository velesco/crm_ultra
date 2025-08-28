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
        Schema::create('communication_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('type', 30); // email_outbound, sms_outbound, whatsapp_inbound, etc.
            $table->string('channel', 20); // email, sms, whatsapp, system
            $table->integer('count')->default(0);
            $table->decimal('total_cost', 10, 4)->default(0); // Total cost for the day
            $table->json('additional_data')->nullable(); // Extra metrics
            $table->timestamps();

            // Unique constraint to prevent duplicates
            $table->unique(['date', 'type', 'channel']);

            // Indexes for performance
            $table->index(['date', 'type']);
            $table->index(['channel', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_stats');
    }
};
