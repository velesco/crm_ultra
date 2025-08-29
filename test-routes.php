<?php

require_once 'vendor/autoload.php';

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get route collection
$routes = app('router')->getRoutes();

// Test WhatsApp routes specifically
$whatsappRoutes = [];
foreach ($routes as $route) {
    $name = $route->getName();
    if ($name && str_starts_with($name, 'whatsapp.')) {
        $whatsappRoutes[$name] = [
            'name' => $name,
            'uri' => $route->uri(),
            'methods' => $route->methods(),
        ];
    }
}

echo "WhatsApp Routes Found:\n";
echo "======================\n";
foreach ($whatsappRoutes as $route) {
    echo sprintf("%-25s | %-10s | %s\n", 
        $route['name'], 
        implode(',', $route['methods']), 
        $route['uri']
    );
}

// Check specific routes we need
$requiredRoutes = ['whatsapp.send', 'whatsapp.send-message', 'whatsapp.index'];
echo "\nRequired Route Check:\n";
echo "=====================\n";
foreach ($requiredRoutes as $routeName) {
    $exists = isset($whatsappRoutes[$routeName]) ? '✅ EXISTS' : '❌ MISSING';
    echo "$routeName: $exists\n";
}

echo "\nTotal WhatsApp routes: " . count($whatsappRoutes) . "\n";
