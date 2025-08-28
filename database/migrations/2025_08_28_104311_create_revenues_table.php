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
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();

            // Revenue identification
            $table->string('transaction_id')->unique();
            $table->string('reference_id')->nullable(); // External reference (invoice, order, etc.)

            // Revenue details
            $table->decimal('amount', 15, 2); // Revenue amount
            $table->string('currency', 3)->default('USD');
            $table->string('type')->index(); // subscription, one_time, commission, etc.
            $table->string('status')->default('pending'); // pending, confirmed, refunded, cancelled

            // Source information
            $table->string('source_type'); // email_campaign, sms_campaign, whatsapp_message, manual
            $table->unsignedBigInteger('source_id')->nullable(); // ID of the source (campaign, message, etc.)
            $table->string('channel')->index(); // email, sms, whatsapp, direct, api

            // Customer information
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('set null');
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();

            // Financial details
            $table->decimal('cost', 15, 2)->default(0); // Associated costs (SMS, Email provider costs, etc.)
            $table->decimal('net_revenue', 15, 2)->storedAs('amount - cost'); // Calculated net revenue
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('commission', 15, 2)->default(0);

            // Timing
            $table->timestamp('revenue_date'); // When the revenue was actually earned
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            // Additional metadata
            $table->json('metadata')->nullable(); // Additional data (subscription details, campaign metrics, etc.)
            $table->text('notes')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('created_source')->default('system'); // system, manual, import, api

            $table->timestamps();

            // Indexes for performance
            $table->index(['revenue_date', 'status']);
            $table->index(['contact_id', 'revenue_date']);
            $table->index(['source_type', 'source_id']);
            $table->index(['channel', 'revenue_date']);
            $table->index(['type', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};
