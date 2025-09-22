<?php

// Test pentru verificarea route-urilor Google Sheets
echo "Testing Google Sheets routes...\n\n";

// Testăm sintaxa
echo "Checking syntax first...\n";
$syntaxCheck = shell_exec('cd /Users/vasilevelesco/Documents/crm_ultra && php -l routes/web.php 2>&1');
echo $syntaxCheck . "\n";

if (strpos($syntaxCheck, 'No syntax errors') !== false) {
    echo "✅ SYNTAX OK!\n\n";
    
    // Testăm route-urile Google Sheets
    echo "Checking Google Sheets routes...\n";
    $routesList = shell_exec('cd /Users/vasilevelesco/Documents/crm_ultra && php artisan route:list --name=google.sheets 2>&1');
    
    if ($routesList) {
        echo "Google Sheets routes found:\n";
        echo $routesList . "\n";
    } else {
        echo "No Google Sheets routes found or error occurred\n";
    }
    
    // Check specific route
    echo "\nChecking if google.sheets.index exists...\n";
    $specificRoute = shell_exec('cd /Users/vasilevelesco/Documents/crm_ultra && php artisan route:list --name=google.sheets.index 2>&1');
    
    if ($specificRoute) {
        echo "✅ google.sheets.index route found:\n";
        echo $specificRoute . "\n";
    } else {
        echo "❌ google.sheets.index route NOT found\n";
    }
    
} else {
    echo "❌ SYNTAX ERRORS - Cannot check routes!\n";
    echo "Details: " . $syntaxCheck . "\n";
}
