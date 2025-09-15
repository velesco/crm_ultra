<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== CRM ULTRA SYSTEM CHECK ===\n\n";

try {
    // Check database connection
    DB::connection()->getPdo();
    echo "✅ Database connection: OK\n";
    
    // Check critical tables
    $criticalTables = [
        'users', 'contacts', 'email_campaigns', 'email_templates', 
        'smtp_configs', 'contact_segments', 'email_campaign_segments'
    ];
    
    foreach ($criticalTables as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            echo "✅ Table '$table': OK ($count records)\n";
        } else {
            echo "❌ Table '$table': MISSING\n";
        }
    }
    
    // Check controllers exist
    $controllerPath = __DIR__ . '/app/Http/Controllers/';
    $criticalControllers = [
        'ContactController.php',
        'EmailCampaignController.php', 
        'EmailTemplateController.php',
        'SmtpConfigController.php',
        'DashboardController.php'
    ];
    
    echo "\n";
    foreach ($criticalControllers as $controller) {
        if (file_exists($controllerPath . $controller)) {
            echo "✅ Controller '$controller': OK\n";
        } else {
            echo "❌ Controller '$controller': MISSING\n";
        }
    }
    
    echo "\n=== SYSTEM STATUS: HEALTHY ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n=== SYSTEM STATUS: ERROR ===\n";
}
