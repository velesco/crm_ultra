<?php
/**
 * 🚀 CRM Ultra - Script Verificare Configurare PHP
 * 
 * Acest script verifică toate configurările necesare pentru CRM Ultra
 */

echo "============================================\n";
echo "🚀 CRM Ultra - Verificare Configurare PHP\n";
echo "============================================\n\n";

// Verificare Laravel Environment
try {
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "✅ Laravel Environment: LOADED\n";
} catch (Exception $e) {
    echo "❌ Laravel Environment: ERROR - " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n📋 Verificare Configurații...\n";

// Verificare Environment Variables
$requiredEnvVars = [
    'APP_NAME' => 'Nume aplicație',
    'APP_KEY' => 'Cheie aplicație',
    'APP_URL' => 'URL aplicație',
    'DB_CONNECTION' => 'Tip conexiune DB',
    'DB_HOST' => 'Host DB',
    'DB_DATABASE' => 'Nume DB',
    'DB_USERNAME' => 'Username DB',
];

foreach ($requiredEnvVars as $var => $description) {
    $value = env($var);
    if (!empty($value)) {
        echo "✅ $var ($description): CONFIGURAT\n";
    } else {
        echo "❌ $var ($description): LIPSEȘTE\n";
    }
}

// Verificare configurații opționale
echo "\n📋 Verificare Configurații Opționale...\n";

$optionalEnvVars = [
    'GOOGLE_CLIENT_ID' => 'Google OAuth Client ID',
    'GOOGLE_CLIENT_SECRET' => 'Google OAuth Secret',
    'TWILIO_ACCOUNT_SID' => 'Twilio Account SID',
    'TWILIO_AUTH_TOKEN' => 'Twilio Auth Token',
    'VONAGE_KEY' => 'Vonage API Key',
    'VONAGE_SECRET' => 'Vonage API Secret',
    'WHATSAPP_SERVER_URL' => 'WhatsApp Server URL',
    'WHATSAPP_API_TOKEN' => 'WhatsApp API Token',
];

foreach ($optionalEnvVars as $var => $description) {
    $value = env($var);
    if (!empty($value)) {
        echo "✅ $var ($description): CONFIGURAT\n";
    } else {
        echo "⚠️  $var ($description): LIPSEȘTE (opțional)\n";
    }
}

// Test conexiune baza de date
echo "\n📋 Test Conexiune Baza de Date...\n";
try {
    DB::connection()->getPdo();
    echo "✅ Conexiune DB: SUCCESS\n";
    
    // Verificare tabele
    $tables = DB::select('SHOW TABLES');
    echo "✅ Număr tabele în DB: " . count($tables) . "\n";
    
    // Verificare migrări
    try {
        $migrations = DB::table('migrations')->count();
        echo "✅ Migrări rulate: $migrations\n";
    } catch (Exception $e) {
        echo "⚠️  Tabela migrations nu există (rulează: php artisan migrate)\n";
    }
    
} catch (Exception $e) {
    echo "❌ Conexiune DB: ERROR - " . $e->getMessage() . "\n";
}

// Verificare cache și configurări
echo "\n📋 Verificare Cache și Configurări...\n";

if (file_exists(base_path('bootstrap/cache/config.php'))) {
    echo "✅ Config Cache: ACTIV\n";
} else {
    echo "⚠️  Config Cache: INACTIV (pentru producție rulează: php artisan config:cache)\n";
}

if (file_exists(base_path('bootstrap/cache/routes-v7.php'))) {
    echo "✅ Route Cache: ACTIV\n";
} else {
    echo "⚠️  Route Cache: INACTIV (pentru producție rulează: php artisan route:cache)\n";
}

// Verificare storage permissions
echo "\n📋 Verificare Permisiuni...\n";

$directories = [
    'storage/app',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache'
];

foreach ($directories as $dir) {
    $path = base_path($dir);
    if (is_writable($path)) {
        echo "✅ $dir: WRITABLE\n";
    } else {
        echo "❌ $dir: NOT WRITABLE (rulează: chmod -R 775 $dir)\n";
    }
}

// Test email configuration
echo "\n📋 Test Configurație Email...\n";
try {
    $mailer = env('MAIL_MAILER');
    $host = env('MAIL_HOST');
    $port = env('MAIL_PORT');
    
    if (!empty($mailer) && !empty($host)) {
        echo "✅ Mail Mailer: $mailer\n";
        echo "✅ Mail Host: $host:$port\n";
        
        // Test simplu de configurare SMTP
        if ($mailer === 'smtp' && !empty($host)) {
            echo "✅ SMTP Configuration: CONFIGURED\n";
        }
    } else {
        echo "⚠️  Email Configuration: INCOMPLETE\n";
    }
} catch (Exception $e) {
    echo "❌ Email Test: ERROR - " . $e->getMessage() . "\n";
}

// Verificare Google Services
echo "\n📋 Verificare Google Services...\n";
$googleClientId = env('GOOGLE_CLIENT_ID');
$googleSecret = env('GOOGLE_CLIENT_SECRET');

if (!empty($googleClientId) && !empty($googleSecret)) {
    echo "✅ Google OAuth: CONFIGURED\n";
    
    // Verificare redirect URI
    $redirectUri = env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/google-sheets/callback');
    echo "✅ Google Redirect URI: $redirectUri\n";
} else {
    echo "⚠️  Google OAuth: NOT CONFIGURED (necesar pentru Gmail/Sheets)\n";
}

// Verificare SMS Services
echo "\n📋 Verificare SMS Services...\n";
$hasTwilio = !empty(env('TWILIO_ACCOUNT_SID')) && !empty(env('TWILIO_AUTH_TOKEN'));
$hasVonage = !empty(env('VONAGE_KEY')) && !empty(env('VONAGE_SECRET'));
$hasOrange = !empty(env('ORANGE_API_KEY')) && !empty(env('ORANGE_API_SECRET'));

if ($hasTwilio) {
    echo "✅ Twilio SMS: CONFIGURED\n";
}
if ($hasVonage) {
    echo "✅ Vonage SMS: CONFIGURED\n";
}
if ($hasOrange) {
    echo "✅ Orange SMS: CONFIGURED\n";
}

if (!$hasTwilio && !$hasVonage && !$hasOrange) {
    echo "⚠️  SMS Services: NONE CONFIGURED (opțional)\n";
}

// Verificare WhatsApp
echo "\n📋 Verificare WhatsApp...\n";
$whatsappUrl = env('WHATSAPP_SERVER_URL');
$whatsappToken = env('WHATSAPP_API_TOKEN');

if (!empty($whatsappUrl) && !empty($whatsappToken)) {
    echo "✅ WhatsApp Configuration: CONFIGURED\n";
    echo "✅ WhatsApp Server URL: $whatsappUrl\n";
} else {
    echo "⚠️  WhatsApp Configuration: NOT CONFIGURED (opțional)\n";
}

// Verificare Queue Configuration
echo "\n📋 Verificare Queue...\n";
$queueConnection = env('QUEUE_CONNECTION', 'sync');
echo "✅ Queue Driver: $queueConnection\n";

if ($queueConnection !== 'sync') {
    echo "✅ Background Jobs: ENABLED\n";
    echo "ℹ️  Pentru a porni worker: php artisan queue:work\n";
} else {
    echo "⚠️  Background Jobs: SYNC MODE (pentru producție folosește 'database' sau 'redis')\n";
}

// Sumar final
echo "\n==============================================\n";
echo "📊 SUMAR VERIFICARE\n";
echo "==============================================\n\n";

echo "🎯 STATUS GENERAL:\n";

// Count configurations
$totalConfigs = count($requiredEnvVars) + count($optionalEnvVars);
$configuredCount = 0;

foreach (array_merge($requiredEnvVars, $optionalEnvVars) as $var => $desc) {
    if (!empty(env($var))) {
        $configuredCount++;
    }
}

$percentage = round(($configuredCount / $totalConfigs) * 100);
echo "✅ Configurare generală: $percentage% ($configuredCount/$totalConfigs)\n";

// Database status
try {
    DB::connection()->getPdo();
    echo "✅ Baza de date: FUNCȚIONALĂ\n";
} catch (Exception $e) {
    echo "❌ Baza de date: PROBLEME\n";
}

// Essential services status
$essentialServices = 0;
$configuredServices = 0;

if (!empty(env('GOOGLE_CLIENT_ID'))) {
    $configuredServices++;
}
$essentialServices++;

if (!empty(env('TWILIO_ACCOUNT_SID')) || !empty(env('VONAGE_KEY'))) {
    $configuredServices++;
}
$essentialServices++;

if (!empty(env('WHATSAPP_SERVER_URL'))) {
    $configuredServices++;
}
$essentialServices++;

echo "✅ Servicii externe: $configuredServices/$essentialServices configurate\n";

echo "\n🔧 URMĂTORII PAȘI:\n\n";

if (empty(env('GOOGLE_CLIENT_ID'))) {
    echo "1. Configurează Google OAuth pentru Gmail/Sheets integration\n";
}

if (empty(env('TWILIO_ACCOUNT_SID')) && empty(env('VONAGE_KEY'))) {
    echo "2. Configurează cel puțin un provider SMS (Twilio sau Vonage)\n";
}

if (env('QUEUE_CONNECTION') === 'sync') {
    echo "3. Configurează queue driver pentru background jobs\n";
}

if (!file_exists(base_path('bootstrap/cache/config.php'))) {
    echo "4. Pentru producție: php artisan config:cache\n";
}

echo "\n🎉 Verificare completă!\n";
echo "==============================================\n";

// Return appropriate exit code
if ($percentage >= 80) {
    exit(0); // Success
} else {
    exit(1); // Needs more configuration
}
