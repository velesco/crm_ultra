<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Contact;
use App\Http\Controllers\CommunicationController;
use App\Services\EmailService;
use App\Services\SmsService;
use App\Services\WhatsAppService;

echo "=== TESTING COMMUNICATION CONTROLLER ===\n\n";

try {
    // Get the first contact for testing
    $contact = Contact::first();
    
    if (!$contact) {
        echo "⚠️ No contacts found for testing\n";
        echo "Creating a test contact...\n";
        
        $contact = Contact::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '+40123456789',
            'status' => 'active',
            'created_by' => 1,
        ]);
        
        echo "✅ Test contact created: {$contact->full_name}\n";
    } else {
        echo "✅ Found test contact: {$contact->full_name}\n";
    }
    
    // Test the conversation method logic (without web request)
    $emails = \App\Models\EmailLog::where('contact_id', $contact->id)
        ->orderBy('created_at')
        ->get()
        ->map(function ($email) {
            return [
                'id' => $email->id,
                'type' => 'email',
                'direction' => 'outbound',
                'content' => $email->subject,
                'status' => $email->status,
                'created_at' => $email->created_at,
                'read_at' => $email->read_at,
                'data' => $email,
            ];
        });

    $smsMessages = \App\Models\SmsMessage::where('contact_id', $contact->id)
        ->orderBy('created_at')
        ->get()
        ->map(function ($sms) {
            return [
                'id' => $sms->id,
                'type' => 'sms',
                'direction' => 'outbound',
                'content' => $sms->message,
                'status' => $sms->status,
                'created_at' => $sms->created_at,
                'read_at' => $sms->delivered_at,
                'data' => $sms,
            ];
        });

    $whatsappMessages = \App\Models\WhatsAppMessage::where('contact_id', $contact->id)
        ->orderBy('created_at')
        ->get()
        ->map(function ($whatsapp) {
            return [
                'id' => $whatsapp->id,
                'type' => 'whatsapp',
                'direction' => $whatsapp->direction,
                'content' => $whatsapp->content,
                'status' => $whatsapp->status,
                'created_at' => $whatsapp->created_at,
                'read_at' => $whatsapp->read_at,
                'data' => $whatsapp,
            ];
        });

    // Merge and sort all communications by date
    $allCommunications = $emails
        ->concat($smsMessages)
        ->concat($whatsappMessages)
        ->sortBy('created_at');

    echo "Communications found:\n";
    echo "- Emails: {$emails->count()}\n";
    echo "- SMS: {$smsMessages->count()}\n";
    echo "- WhatsApp: {$whatsappMessages->count()}\n";
    echo "- Total: {$allCommunications->count()}\n";

    // Test the problematic contactStats creation
    $contactStats = [
        'total_emails' => $emails->count(),
        'total_sms' => $smsMessages->count(),
        'total_whatsapp' => $whatsappMessages->count(),
        'first_contact' => $allCommunications->first()['created_at'] ?? null,
        'last_contact' => $allCommunications->last()['created_at'] ?? null,
        'unread_messages' => $whatsappMessages->where('direction', 'inbound')->whereNull('read_at')->count(),
    ];
    
    echo "\n✅ Contact stats created successfully:\n";
    echo "- Total emails: {$contactStats['total_emails']}\n";
    echo "- Total SMS: {$contactStats['total_sms']}\n";
    echo "- Total WhatsApp: {$contactStats['total_whatsapp']}\n";
    echo "- First contact: " . ($contactStats['first_contact'] ? $contactStats['first_contact'] : 'None') . "\n";
    echo "- Last contact: " . ($contactStats['last_contact'] ? $contactStats['last_contact'] : 'None') . "\n";
    echo "- Unread messages: {$contactStats['unread_messages']}\n";
    
    echo "\n=== COMMUNICATION CONTROLLER TEST PASSED ===\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
