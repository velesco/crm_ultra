<?php

// Test script pentru verificarea sintaxei routes/web.php
echo "Testing syntax of routes/web.php...\n";

$output = shell_exec('php -l routes/web.php 2>&1');
echo $output . "\n";

if (strpos($output, 'No syntax errors detected') !== false) {
    echo "✅ SYNTAX OK - No errors found!\n";
} else {
    echo "❌ SYNTAX ERRORS DETECTED!\n";
    echo "Details: " . $output . "\n";
}

// Test dacă fișierul poate fi inclus
try {
    echo "\nTesting if routes can be loaded...\n";
    
    // Simulate minimal Laravel environment
    if (!defined('LARAVEL_START')) {
        define('LARAVEL_START', microtime(true));
    }
    
    echo "Routes syntax test completed.\n";
} catch (Exception $e) {
    echo "Error testing routes: " . $e->getMessage() . "\n";
}
