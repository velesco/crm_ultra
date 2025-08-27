<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if we need to modify the enum - get current column definition
        $result = DB::select("SHOW COLUMNS FROM contacts WHERE Field = 'status'");
        
        if (!empty($result)) {
            $columnType = $result[0]->Type;
            
            // Check if 'prospect' and 'customer' are already in the enum
            $hasProspect = strpos($columnType, "'prospect'") !== false;
            $hasCustomer = strpos($columnType, "'customer'") !== false;
            
            if (!$hasProspect || !$hasCustomer) {
                // Update any potentially invalid status values to 'active' before changing enum
                DB::table('contacts')
                    ->whereNotIn('status', ['active', 'inactive', 'blocked'])
                    ->update(['status' => 'active']);

                // Modify the column to include the new enum values
                DB::statement("ALTER TABLE contacts MODIFY COLUMN status ENUM('active', 'inactive', 'blocked', 'prospect', 'customer') DEFAULT 'active'");
                
                echo "✅ Contact status ENUM updated to include 'prospect' and 'customer'\n";
            } else {
                echo "✅ Contact status ENUM already contains required values\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, update any new status values to 'active'
        DB::table('contacts')
            ->whereIn('status', ['prospect', 'customer'])
            ->update(['status' => 'active']);

        // Then revert to the original enum values
        DB::statement("ALTER TABLE contacts MODIFY COLUMN status ENUM('active', 'inactive', 'blocked') DEFAULT 'active'");
    }
};
