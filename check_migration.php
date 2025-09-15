<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== CHECKING email_campaign_segments TABLE ===\n\n";

try {
    // Check if table exists
    if (Schema::hasTable('email_campaign_segments')) {
        echo "✅ Table 'email_campaign_segments' EXISTS\n\n";
        
        // Get table structure
        $columns = DB::select("DESCRIBE email_campaign_segments");
        echo "TABLE STRUCTURE:\n";
        foreach ($columns as $column) {
            echo "- {$column->Field}: {$column->Type} {$column->Null} {$column->Key}\n";
        }
        
        // Check if the migration record exists
        $migrationExists = DB::table('migrations')
            ->where('migration', '2025_09_15_133704_create_email_campaign_segments_table')
            ->exists();
            
        echo "\nMIGRATION RECORD EXISTS: " . ($migrationExists ? "YES" : "NO") . "\n";
        
        if (!$migrationExists) {
            echo "\n🔧 SOLUTION: Insert migration record manually\n";
            DB::table('migrations')->insert([
                'migration' => '2025_09_15_133704_create_email_campaign_segments_table',
                'batch' => DB::table('migrations')->max('batch') + 1
            ]);
            echo "✅ Migration record inserted successfully!\n";
        }
        
    } else {
        echo "❌ Table 'email_campaign_segments' does NOT exist\n";
        echo "Migration should create it normally.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
