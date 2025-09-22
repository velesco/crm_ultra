<?php

// Test pentru verificarea route-urilor duplicate
echo "Testing for duplicate routes in routes/web.php...\n\n";

// Simulăm comanda php artisan route:list care ar trebui să funcționeze acum
$output = shell_exec('cd /Users/vasilevelesco/Documents/crm_ultra && php artisan route:list --name=gmail 2>&1');

if ($output) {
    echo "Gmail routes found:\n";
    echo $output . "\n";
} else {
    echo "No Gmail routes found or error occurred\n";
}

// Testăm sintaxa
echo "\nTesting syntax...\n";
$syntaxCheck = shell_exec('cd /Users/vasilevelesco/Documents/crm_ultra && php -l routes/web.php 2>&1');
echo $syntaxCheck . "\n";

if (strpos($syntaxCheck, 'No syntax errors') !== false) {
    echo "✅ SYNTAX OK!\n";
} else {
    echo "❌ SYNTAX ERRORS DETECTED!\n";
}
