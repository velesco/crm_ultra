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
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->string('from_name')->nullable()->after('content');
            $table->string('from_email')->nullable()->after('from_name');
            $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->after('template_id');
            
            // Make smtp_config_id nullable since it's optional for drafts
            $table->dropForeign(['smtp_config_id']);
            $table->foreignId('smtp_config_id')->nullable()->change();
            $table->foreign('smtp_config_id')->references('id')->on('smtp_configs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropForeign(['email_template_id']);
            $table->dropColumn(['from_name', 'from_email', 'email_template_id']);
            
            // Restore original smtp_config_id constraint
            $table->dropForeign(['smtp_config_id']);
            $table->foreignId('smtp_config_id')->constrained('smtp_configs')->change();
        });
    }
};
