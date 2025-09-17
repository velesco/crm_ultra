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
        Schema::create('email_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained()->onDelete('cascade');
            $table->string('gmail_attachment_id')->nullable(); // Gmail attachment ID
            $table->string('filename');
            $table->string('original_filename')->nullable(); // In case we sanitize
            $table->string('mime_type');
            $table->bigInteger('size_bytes');
            $table->string('content_id')->nullable(); // For inline attachments
            $table->boolean('is_inline')->default(false);
            $table->string('storage_path')->nullable(); // Local file path
            $table->string('storage_disk')->default('local'); // Storage disk
            $table->string('file_hash', 64)->index(); // SHA256 for deduplication
            $table->boolean('is_downloaded')->default(false);
            $table->timestamp('downloaded_at')->nullable();
            $table->json('metadata')->nullable(); // Additional file metadata
            $table->timestamps();

            // Indexes for performance
            $table->index(['email_id', 'is_inline']);
            $table->index(['file_hash', 'size_bytes']); // For deduplication
            $table->index(['mime_type', 'size_bytes']);
            $table->index('is_downloaded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_attachments');
    }
};
