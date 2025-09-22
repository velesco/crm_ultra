<?php

// Test pentru verificarea metodei duplicate din SettingsController
echo "Testing for duplicate methods in SettingsController...\n\n";

// Testăm sintaxa PHP
echo "Checking PHP syntax...\n";
$syntaxCheck = shell_exec('cd /Users/vasilevelesco/Documents/crm_ultra && php -l app/Http/Controllers/SettingsController.php 2>&1');
echo $syntaxCheck . "\n";

if (strpos($syntaxCheck, 'No syntax errors') !== false) {
    echo "✅ SETTINGS CONTROLLER SYNTAX OK!\n\n";
    
    // Testăm din nou route-urile
    echo "Testing routes again...\n";
    $routeTest = shell_exec('cd /Users/vasilevelesco/Documents/crm_ultra && php artisan route:list --name=google.sheets.index 2>&1');
    
    if (strpos($routeTest, 'google.sheets.index') !== false) {
        echo "✅ Routes working! google.sheets.index found\n";
        echo $routeTest . "\n";
    } else {
        echo "❌ Route test failed\n";
        echo $routeTest . "\n";
    }
    
} else {
    echo "❌ SYNTAX ERROR IN SETTINGS CONTROLLER!\n";
    echo "Details: " . $syntaxCheck . "\n";
}

echo "\n=== FINAL TEST ===\n";
echo "Attempting to start Laravel application...\n";

$serverTest = shell_exec('cd /Users/vasilevelesco/Documents/crm_ultra && timeout 5 php artisan serve --port=8001 2>&1 || echo "Server test completed"');
echo $serverTest . "\n";
