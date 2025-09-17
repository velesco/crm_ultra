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
        Schema::create('google_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('team_id')->nullable(); // No foreign key constraint for now
            $table->string('email')->index();
            $table->enum('provider', ['google'])->default('google');
            $table->json('scopes'); // Gmail, Sheets, etc.
            $table->text('access_token_encrypted'); // Encrypted storage
            $table->text('refresh_token_encrypted')->nullable(); // Encrypted storage
            $table->timestamp('token_expires_at')->nullable();
            $table->enum('visibility', ['team', 'private'])->default('private');
            $table->enum('status', ['active', 'inactive', 'expired', 'revoked'])->default('active');
            $table->timestamp('last_sync_at')->nullable();
            $table->json('sync_settings')->nullable(); // Custom sync preferences
            $table->boolean('auto_sync_enabled')->default(true);
            $table->integer('sync_frequency_minutes')->default(15); // How often to sync
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['team_id', 'visibility']);
            $table->index(['email', 'provider']);
            $table->unique(['email', 'user_id']); // One email per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_accounts');
    }
};
