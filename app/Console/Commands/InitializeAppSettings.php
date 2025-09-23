<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitializeAppSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:settings:init {--force : Force initialization even if settings already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize default application settings for all service categories';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Initializing CRM Ultra App Settings...');
        $this->newLine();

        // Check if settings already exist
        $existingCount = AppSetting::count();
        if ($existingCount > 0 && !$this->option('force')) {
            $this->warn("⚠️  Found {$existingCount} existing settings.");
            
            if (!$this->confirm('Do you want to continue? This will create additional settings but won\'t overwrite existing ones.')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        if ($this->option('force') && $existingCount > 0) {
            $this->warn("⚠️  Force mode: This will overwrite existing settings if keys match.");
            if (!$this->confirm('Are you sure you want to continue?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $progressBar = $this->output->createProgressBar(count($this->getDefaultSettings()));

        DB::beginTransaction();

        try {
            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($this->getDefaultSettings() as $setting) {
                $existing = AppSetting::where('key', $setting['key'])->first();
                
                if ($existing) {
                    if ($this->option('force')) {
                        $existing->update($setting);
                        $updated++;
                        $this->info(" ✅ Updated: {$setting['key']}");
                    } else {
                        $skipped++;
                    }
                } else {
                    AppSetting::create($setting);
                    $created++;
                    $this->info(" ✅ Created: {$setting['key']}");
                }
                
                $progressBar->advance();
            }

            DB::commit();
            $progressBar->finish();

            $this->newLine(2);
            $this->info('🎉 App Settings initialization completed successfully!');
            $this->newLine();
            
            $this->table(['Action', 'Count'], [
                ['Created', $created],
                ['Updated', $updated], 
                ['Skipped', $skipped],
                ['Total', $created + $updated + $skipped]
            ]);

            $this->newLine();
            $this->info('💡 You can now configure these settings via:');
            $this->line('   • Web Interface: Admin Panel → App Settings');
            $this->line('   • Artisan Command: php artisan app:settings:show');
            $this->newLine();
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $progressBar->finish();
            
            $this->newLine(2);
            $this->error('❌ Error initializing settings: ' . $e->getMessage());
            $this->error('Transaction rolled back. No changes were made.');
            
            return Command::FAILURE;
        }
    }

    /**
     * Get default settings configuration
     */
    private function getDefaultSettings(): array
    {
        return [
            // Google API Settings
            [
                'key' => 'google.client_id',
                'label' => 'Google Client ID',
                'category' => 'google',
                'type' => 'string',
                'description' => 'Google OAuth Client ID for Gmail and Sheets API access',
                'is_env_synced' => true,
                'env_key' => 'GOOGLE_CLIENT_ID',
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 1,
            ],
            [
                'key' => 'google.client_secret',
                'label' => 'Google Client Secret',
                'category' => 'google',
                'type' => 'encrypted',
                'description' => 'Google OAuth Client Secret (encrypted)',
                'is_encrypted' => true,
                'is_env_synced' => true,
                'env_key' => 'GOOGLE_CLIENT_SECRET',
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 2,
            ],
            [
                'key' => 'google.redirect_uri',
                'label' => 'Google Redirect URI',
                'category' => 'google',
                'type' => 'string',
                'description' => 'Google OAuth Redirect URI for authentication callbacks',
                'is_env_synced' => true,
                'env_key' => 'GOOGLE_REDIRECT_URI',
                'validation_rules' => ['nullable', 'url'],
                'sort_order' => 3,
            ],

            // SMS Settings - Twilio
            [
                'key' => 'sms.twilio.sid',
                'label' => 'Twilio Account SID',
                'category' => 'sms',
                'type' => 'string',
                'description' => 'Twilio Account SID for SMS service',
                'is_env_synced' => true,
                'env_key' => 'TWILIO_ACCOUNT_SID',
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 1,
            ],
            [
                'key' => 'sms.twilio.token',
                'label' => 'Twilio Auth Token',
                'category' => 'sms',
                'type' => 'encrypted',
                'description' => 'Twilio Auth Token (encrypted)',
                'is_encrypted' => true,
                'is_env_synced' => true,
                'env_key' => 'TWILIO_AUTH_TOKEN',
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 2,
            ],
            [
                'key' => 'sms.twilio.from',
                'label' => 'Twilio From Number',
                'category' => 'sms',
                'type' => 'string',
                'description' => 'Twilio phone number to send SMS from',
                'is_env_synced' => true,
                'env_key' => 'TWILIO_FROM_NUMBER',
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 3,
            ],

            // SMS Settings - Vonage
            [
                'key' => 'sms.vonage.key',
                'label' => 'Vonage API Key',
                'category' => 'sms',
                'type' => 'string',
                'description' => 'Vonage (Nexmo) API Key for SMS service',
                'is_env_synced' => true,
                'env_key' => 'VONAGE_API_KEY',
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 4,
            ],
            [
                'key' => 'sms.vonage.secret',
                'label' => 'Vonage API Secret',
                'category' => 'sms',
                'type' => 'encrypted',
                'description' => 'Vonage (Nexmo) API Secret (encrypted)',
                'is_encrypted' => true,
                'is_env_synced' => true,
                'env_key' => 'VONAGE_API_SECRET',
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 5,
            ],
            [
                'key' => 'sms.vonage.from',
                'label' => 'Vonage From Name',
                'category' => 'sms',
                'type' => 'string',
                'description' => 'Vonage sender name or number',
                'is_env_synced' => true,
                'env_key' => 'VONAGE_FROM',
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 6,
            ],

            // SMS Settings - Orange
            [
                'key' => 'sms.orange.username',
                'label' => 'Orange SMS Username',
                'category' => 'sms',
                'type' => 'string',
                'description' => 'Orange SMS API Username',
                'is_env_synced' => true,
                'env_key' => 'ORANGE_SMS_USERNAME',
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 7,
            ],
            [
                'key' => 'sms.orange.password',
                'label' => 'Orange SMS Password',
                'category' => 'sms',
                'type' => 'encrypted',
                'description' => 'Orange SMS API Password (encrypted)',
                'is_encrypted' => true,
                'is_env_synced' => true,
                'env_key' => 'ORANGE_SMS_PASSWORD',
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 8,
            ],

            // WhatsApp Settings
            [
                'key' => 'whatsapp.server_url',
                'label' => 'WhatsApp Server URL',
                'category' => 'whatsapp',
                'type' => 'string',
                'description' => 'WhatsApp Server URL for API communication',
                'is_env_synced' => true,
                'env_key' => 'WHATSAPP_SERVER_URL',
                'validation_rules' => ['nullable', 'url'],
                'sort_order' => 1,
            ],
            [
                'key' => 'whatsapp.webhook_url',
                'label' => 'WhatsApp Webhook URL',
                'category' => 'whatsapp',
                'type' => 'string',
                'description' => 'WhatsApp webhook URL for message callbacks',
                'is_env_synced' => true,
                'env_key' => 'WHATSAPP_WEBHOOK_URL',
                'validation_rules' => ['nullable', 'url'],
                'sort_order' => 2,
            ],

            // Email Settings
            [
                'key' => 'mail.default_from_name',
                'label' => 'Default From Name',
                'category' => 'email',
                'type' => 'string',
                'description' => 'Default sender name for outgoing emails',
                'is_env_synced' => true,
                'env_key' => 'MAIL_FROM_NAME',
                'validation_rules' => ['nullable', 'string', 'max:255'],
                'sort_order' => 1,
            ],
            [
                'key' => 'mail.default_from_email',
                'label' => 'Default From Email',
                'category' => 'email',
                'type' => 'string',
                'description' => 'Default sender email address',
                'is_env_synced' => true,
                'env_key' => 'MAIL_FROM_ADDRESS',
                'validation_rules' => ['nullable', 'email'],
                'sort_order' => 2,
            ],

            // Database Settings
            [
                'key' => 'database.backup_schedule',
                'label' => 'Backup Schedule',
                'category' => 'database',
                'type' => 'string',
                'description' => 'Cron expression for automatic database backups',
                'value' => '0 2 * * *', // Daily at 2 AM
                'sort_order' => 1,
            ],
            [
                'key' => 'database.retention_days',
                'label' => 'Backup Retention Days',
                'category' => 'database',
                'type' => 'integer',
                'description' => 'Number of days to keep database backups',
                'value' => 30,
                'validation_rules' => ['integer', 'min:1', 'max:365'],
                'sort_order' => 2,
            ],

            // General Application Settings
            [
                'key' => 'app.name',
                'label' => 'Application Name',
                'category' => 'general',
                'type' => 'string',
                'description' => 'Name of the CRM application',
                'value' => 'CRM Ultra',
                'is_env_synced' => true,
                'env_key' => 'APP_NAME',
                'validation_rules' => ['string', 'max:255'],
                'sort_order' => 1,
            ],
            [
                'key' => 'app.url',
                'label' => 'Application URL',
                'category' => 'general',
                'type' => 'string',
                'description' => 'Base URL of the application',
                'is_env_synced' => true,
                'env_key' => 'APP_URL',
                'validation_rules' => ['url'],
                'sort_order' => 2,
            ],
            [
                'key' => 'app.environment',
                'label' => 'Environment',
                'category' => 'general',
                'type' => 'string',
                'description' => 'Current application environment',
                'value' => 'production',
                'is_env_synced' => true,
                'env_key' => 'APP_ENV',
                'validation_rules' => ['in:local,staging,production'],
                'sort_order' => 3,
            ],
            [
                'key' => 'app.debug',
                'label' => 'Debug Mode',
                'category' => 'general',
                'type' => 'boolean',
                'description' => 'Enable debug mode for development',
                'value' => false,
                'is_env_synced' => true,
                'env_key' => 'APP_DEBUG',
                'sort_order' => 4,
            ],
            [
                'key' => 'app.timezone',
                'label' => 'Timezone',
                'category' => 'general',
                'type' => 'string',
                'description' => 'Default timezone for the application',
                'value' => 'Europe/Bucharest',
                'is_env_synced' => true,
                'env_key' => 'APP_TIMEZONE',
                'validation_rules' => ['string'],
                'sort_order' => 5,
            ],
        ];
    }
}
