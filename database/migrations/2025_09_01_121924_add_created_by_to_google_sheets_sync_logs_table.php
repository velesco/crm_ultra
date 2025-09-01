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
        Schema::table('google_sheets_sync_logs', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('integration_id')->constrained('users');
            $table->index(['created_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('google_sheets_sync_logs', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropIndex(['created_by', 'status']);
            $table->dropColumn('created_by');
        });
    }
};
