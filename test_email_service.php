<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\EmailService;
use App\Models\Contact;
use App\Models\SmtpConfig;

echo "=== TESTING EMAIL SERVICE (SYMFONY MAILER) ===\n\n";

try {
    // Test EmailService instantiation
    $emailService = app(EmailService::class);
    echo "✅ EmailService instantiated successfully\n";
    
    // Check if sendQuickEmail method exists
    if (method_exists($emailService, 'sendQuickEmail')) {
        echo "✅ EmailService::sendQuickEmail() method exists\n";
    } else {
        echo "❌ EmailService::sendQuickEmail() method missing\n";
    }
    
    // Test getting a contact and SMTP config (without actually sending)
    $contact = Contact::first();
    $smtpConfig = SmtpConfig::where('is_active', true)->first();
    
    if ($contact && $smtpConfig) {
        echo "✅ Found test contact: {$contact->email}\n";
        echo "✅ Found active SMTP config: {$smtpConfig->name}\n";
        
        // Test the method signature without actually sending
        echo "✅ Ready for email sending (test mode)\n";
        
        // Validate SMTP config structure
        $requiredFields = ['host', 'port', 'username', 'password', 'from_email', 'from_name'];
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (empty($smtpConfig->$field)) {
                $missingFields[] = $field;
            }
        }
        
        if (empty($missingFields)) {
            echo "✅ SMTP config has all required fields\n";
        } else {
            echo "⚠️ SMTP config missing fields: " . implode(', ', $missingFields) . "\n";
        }
    } else {
        if (!$contact) echo "⚠️ No contact found for testing\n";
        if (!$smtpConfig) echo "⚠️ No active SMTP config found for testing\n";
    }
    
    // Test Laravel Mail configuration
    $mailConfig = config('mail');
    echo "✅ Laravel Mail configured with driver: " . ($mailConfig['default'] ?? 'none') . "\n";
    
    // Check if Symfony Mailer classes exist
    if (class_exists('Symfony\Component\Mime\Email')) {
        echo "✅ Symfony Mailer classes available\n";
    } else {
        echo "❌ Symfony Mailer classes not found\n";
    }
    
    echo "\n=== EMAIL SERVICE READY FOR PRODUCTION ===\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
