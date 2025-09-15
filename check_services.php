<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\EmailService;
use App\Services\SmsService;
use App\Services\WhatsAppService;

echo "=== CHECKING COMMUNICATION SERVICES ===\n\n";

try {
    // Test EmailService
    $emailService = app(EmailService::class);
    echo "✅ EmailService instantiated successfully\n";
    
    // Check if sendQuickEmail method exists
    if (method_exists($emailService, 'sendQuickEmail')) {
        echo "✅ EmailService::sendQuickEmail() method exists\n";
    } else {
        echo "❌ EmailService::sendQuickEmail() method missing\n";
    }
    
    // Test SmsService
    $smsService = app(SmsService::class);
    echo "✅ SmsService instantiated successfully\n";
    
    // Check if sendQuickSms method exists
    if (method_exists($smsService, 'sendQuickSms')) {
        echo "✅ SmsService::sendQuickSms() method exists\n";
    } else {
        echo "❌ SmsService::sendQuickSms() method missing\n";
    }
    
    // Test WhatsAppService
    $whatsappService = app(WhatsAppService::class);
    echo "✅ WhatsAppService instantiated successfully\n";
    
    // Check if sendQuickMessage method exists
    if (method_exists($whatsappService, 'sendQuickMessage')) {
        echo "✅ WhatsAppService::sendQuickMessage() method exists\n";
    } else {
        echo "❌ WhatsAppService::sendQuickMessage() method missing\n";
    }
    
    echo "\n=== SERVICE METHODS CHECK ===\n";
    
    // List all methods in EmailService
    $emailMethods = get_class_methods($emailService);
    echo "EmailService methods: " . implode(', ', $emailMethods) . "\n\n";
    
    // List all methods in SmsService
    $smsMethods = get_class_methods($smsService);
    echo "SmsService methods: " . implode(', ', $smsMethods) . "\n\n";
    
    // List all methods in WhatsAppService
    $whatsappMethods = get_class_methods($whatsappService);
    echo "WhatsAppService methods: " . implode(', ', $whatsappMethods) . "\n\n";
    
    echo "=== ALL SERVICES OPERATIONAL ===\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
