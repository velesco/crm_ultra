<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_segments', function (Blueprint $table) {
            // Add type column as enum with default based on is_dynamic
            $table->enum('type', ['static', 'dynamic'])->after('is_dynamic')->default('static');
        });

        // Update existing records: set type based on is_dynamic
        DB::table('contact_segments')
            ->where('is_dynamic', true)
            ->update(['type' => 'dynamic']);

        DB::table('contact_segments')
            ->where('is_dynamic', false)
            ->update(['type' => 'static']);
    }

    public function down(): void
    {
        Schema::table('contact_segments', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
