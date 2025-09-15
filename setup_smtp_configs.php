<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SmtpConfig;
use App\Models\User;

echo "=== CHECKING & CREATING SMTP CONFIGS ===\n\n";

try {
    // Check if there are any SMTP configs
    $count = SmtpConfig::count();
    echo "Current SMTP configs count: $count\n";
    
    if ($count === 0) {
        echo "Creating test SMTP configurations...\n";
        
        // Get the first user (admin)
        $user = User::first();
        if (!$user) {
            echo "❌ No users found. Please create a user first.\n";
            exit;
        }
        
        // Create test SMTP configurations
        $configs = [
            [
                'name' => 'Gmail SMTP',
                'provider' => 'gmail',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'your-email@gmail.com',
                'password' => 'your-app-password',
                'from_email' => 'your-email@gmail.com',
                'from_name' => 'CRM Ultra',
                'is_active' => true,
                'priority' => 1,
                'daily_limit' => 500,
                'hourly_limit' => 50,
                'created_by' => $user->id,
            ],
            [
                'name' => 'Hostinger SMTP',
                'provider' => 'custom',
                'host' => 'smtp.hostinger.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'noreply@yourdomain.com',
                'password' => 'your-email-password',
                'from_email' => 'noreply@yourdomain.com',
                'from_name' => 'CRM Ultra System',
                'is_active' => true,
                'priority' => 2,
                'daily_limit' => 1000,
                'hourly_limit' => 100,
                'created_by' => $user->id,
            ],
            [
                'name' => 'SendGrid SMTP',
                'provider' => 'sendgrid',
                'host' => 'smtp.sendgrid.net',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'apikey',
                'password' => 'SG.your-api-key',
                'from_email' => 'noreply@yourdomain.com',
                'from_name' => 'CRM Ultra',
                'is_active' => false,
                'priority' => 3,
                'daily_limit' => 40000,
                'hourly_limit' => 4000,
                'created_by' => $user->id,
            ]
        ];
        
        foreach ($configs as $config) {
            $smtp = SmtpConfig::create($config);
            echo "✅ Created: {$smtp->name} (ID: {$smtp->id})\n";
        }
        
        echo "\n✅ Test SMTP configurations created successfully!\n";
    } else {
        echo "SMTP configs already exist. Checking structure...\n";
        $config = SmtpConfig::first();
        if ($config) {
            echo "✅ Sample config: {$config->name}\n";
            echo "✅ Provider field: " . ($config->provider ?? 'NULL') . "\n";
            echo "✅ Priority field: " . ($config->priority ?? 'NULL') . "\n";
        }
    }
    
    // Test the API endpoint that was failing
    echo "\n=== TESTING API ENDPOINT ===\n";
    
    $configs = SmtpConfig::where('is_active', true)
        ->select('id', 'name', 'from_email', 'provider')
        ->orderBy('name')
        ->get();
        
    echo "Found " . $configs->count() . " active SMTP configs:\n";
    foreach ($configs as $config) {
        echo "- {$config->name} ({$config->provider}) - {$config->from_email}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
