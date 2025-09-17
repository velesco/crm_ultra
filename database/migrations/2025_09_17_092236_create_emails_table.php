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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('google_account_id')->constrained()->onDelete('cascade');
            $table->string('thread_id')->index(); // Gmail thread ID
            $table->string('message_id')->unique(); // Gmail message ID
            $table->string('gmail_id')->index(); // Internal Gmail ID
            $table->string('subject')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->string('from_email')->index();
            $table->string('from_name')->nullable();
            $table->json('to_recipients')->nullable(); // Array of recipients
            $table->json('cc_recipients')->nullable();
            $table->json('bcc_recipients')->nullable();
            $table->timestamp('date_sent')->index();
            $table->timestamp('date_received')->index();
            $table->json('labels')->nullable(); // Gmail labels/folders
            $table->boolean('is_read')->default(false)->index();
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_important')->default(false);
            $table->boolean('is_draft')->default(false);
            $table->boolean('is_sent')->default(false);
            $table->boolean('is_trash')->default(false);
            $table->boolean('is_spam')->default(false);
            $table->integer('size_bytes')->nullable(); // Message size
            $table->string('snippet', 500)->nullable(); // Email preview text
            $table->json('headers')->nullable(); // Full email headers
            $table->boolean('has_attachments')->default(false);
            $table->string('in_reply_to')->nullable(); // Message ID this is replying to
            $table->json('references')->nullable(); // Thread references
            $table->timestamps();

            // Indexes for performance and search
            $table->index(['google_account_id', 'date_received']);
            $table->index(['thread_id', 'date_sent']);
            $table->index(['from_email', 'date_sent']);
            $table->index(['is_read', 'date_received']);
            $table->index(['google_account_id', 'is_read']);
            
            // Full-text search index (add manually if needed)
            // $table->fullText(['subject', 'body_text', 'snippet']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
