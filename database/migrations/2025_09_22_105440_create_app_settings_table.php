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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json, encrypted
            $table->string('category')->default('general'); // general, google, sms, whatsapp, email, etc.
            $table->string('label')->nullable(); // Human readable label
            $table->text('description')->nullable(); // Description for the setting
            $table->boolean('is_encrypted')->default(false); // Whether value is encrypted
            $table->boolean('is_env_synced')->default(false); // Whether to sync with .env
            $table->string('env_key')->nullable(); // Corresponding .env key
            $table->json('validation_rules')->nullable(); // Validation rules as JSON
            $table->json('options')->nullable(); // Select options if applicable
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['category', 'is_active']);
            $table->index(['is_env_synced', 'env_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
