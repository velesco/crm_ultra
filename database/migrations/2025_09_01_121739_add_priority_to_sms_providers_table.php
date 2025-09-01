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
        Schema::table('sms_providers', function (Blueprint $table) {
            $table->integer('priority')->default(0)->after('is_active');
            $table->index(['is_active', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_providers', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'priority']);
            $table->dropColumn('priority');
        });
    }
};
