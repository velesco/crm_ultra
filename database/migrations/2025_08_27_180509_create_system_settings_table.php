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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json, text, encrypted
            $table->string('group')->default('general')->index(); // general, email, sms, whatsapp, api, security, etc.
            $table->string('label');
            $table->text('description')->nullable();
            $table->json('validation_rules')->nullable(); // Laravel validation rules
            $table->json('options')->nullable(); // For select/radio options
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_public')->default(false); // Can be accessed by non-admin users
            $table->boolean('requires_restart')->default(false); // Requires app restart/cache clear
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['group', 'sort_order']);
            $table->index(['is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
