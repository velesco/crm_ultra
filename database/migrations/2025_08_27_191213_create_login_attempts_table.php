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
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->enum('type', ['failed', 'success', 'blocked']);
            $table->json('metadata')->nullable(); // Additional data (country, device, etc.)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('blocked_until')->nullable();
            $table->timestamps();

            $table->index(['ip_address', 'created_at']);
            $table->index(['email', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index('blocked_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
