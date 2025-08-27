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
        Schema::table('smtp_configs', function (Blueprint $table) {
            // Rename column from from_address to from_email if it exists
            if (Schema::hasColumn('smtp_configs', 'from_address')) {
                $table->renameColumn('from_address', 'from_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smtp_configs', function (Blueprint $table) {
            // Rename column back from from_email to from_address
            if (Schema::hasColumn('smtp_configs', 'from_email')) {
                $table->renameColumn('from_email', 'from_address');
            }
        });
    }
};
