<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first super admin user for created_by
        $superAdmin = User::role('super_admin')->first();
        $userId = $superAdmin?->id ?? 1;

        $settings = [
            // General Settings
            [
                'key' => 'app.name',
                'label' => 'Application Name',
                'value' => 'CRM Ultra',
                'type' => 'string',
                'group' => 'general',
                'description' => 'The name of the application displayed throughout the system',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'app.timezone',
                'label' => 'Default Timezone',
                'value' => 'UTC',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default timezone for the application',
                'validation_rules' => ['required', 'string'],
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'app.maintenance_mode',
                'label' => 'Maintenance Mode',
                'value' => false,
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Enable maintenance mode to prevent user access',
                'requires_restart' => true,
                'sort_order' => 3,
            ],
            [
                'key' => 'app.max_file_upload_size',
                'label' => 'Max File Upload Size (MB)',
                'value' => 10,
                'type' => 'integer',
                'group' => 'general',
                'description' => 'Maximum file size allowed for uploads in megabytes',
                'validation_rules' => ['required', 'integer', 'min:1', 'max:100'],
                'is_public' => true,
                'sort_order' => 4,
            ],
            
            // Email Settings
            [
                'key' => 'email.default_from_name',
                'label' => 'Default From Name',
                'value' => 'CRM Ultra System',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default name used in email from field',
                'validation_rules' => ['required', 'string', 'max:255'],
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'email.default_from_address',
                'label' => 'Default From Email',
                'value' => 'noreply@crmultra.com',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default email address used in from field',
                'validation_rules' => ['required', 'email'],
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'email.max_daily_limit',
                'label' => 'Max Daily Email Limit',
                'value' => 1000,
                'type' => 'integer',
                'group' => 'email',
                'description' => 'Maximum number of emails that can be sent per day',
                'validation_rules' => ['required', 'integer', 'min:1'],
                'sort_order' => 3,
            ],
            [
                'key' => 'email.bounce_handling_enabled',
                'label' => 'Bounce Handling Enabled',
                'value' => true,
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Enable automatic bounce handling for emails',
                'sort_order' => 4,
            ],
            
            // SMS Settings
            [
                'key' => 'sms.default_provider',
                'label' => 'Default SMS Provider',
                'value' => 'twilio',
                'type' => 'string',
                'group' => 'sms',
                'description' => 'Default SMS provider to use for sending messages',
                'options' => [
                    'twilio' => 'Twilio',
                    'vonage' => 'Vonage (Nexmo)',
                    'orange' => 'Orange SMS'
                ],
                'sort_order' => 1,
            ],
            [
                'key' => 'sms.max_daily_limit',
                'label' => 'Max Daily SMS Limit',
                'value' => 500,
                'type' => 'integer',
                'group' => 'sms',
                'description' => 'Maximum number of SMS messages that can be sent per day',
                'validation_rules' => ['required', 'integer', 'min:1'],
                'sort_order' => 2,
            ],
            [
                'key' => 'sms.delivery_reports_enabled',
                'label' => 'SMS Delivery Reports',
                'value' => true,
                'type' => 'boolean',
                'group' => 'sms',
                'description' => 'Enable delivery report tracking for SMS messages',
                'sort_order' => 3,
            ],
            
            // WhatsApp Settings
            [
                'key' => 'whatsapp.server_url',
                'label' => 'WhatsApp Server URL',
                'value' => 'http://localhost:3001',
                'type' => 'string',
                'group' => 'whatsapp',
                'description' => 'URL of the WhatsApp server for API communication',
                'validation_rules' => ['required', 'url'],
                'sort_order' => 1,
            ],
            [
                'key' => 'whatsapp.api_token',
                'label' => 'WhatsApp API Token',
                'value' => 'your-secure-api-token-here',
                'type' => 'encrypted',
                'group' => 'whatsapp',
                'description' => 'API token for WhatsApp server authentication',
                'is_encrypted' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'whatsapp.max_sessions',
                'label' => 'Maximum WhatsApp Sessions',
                'value' => 10,
                'type' => 'integer',
                'group' => 'whatsapp',
                'description' => 'Maximum number of concurrent WhatsApp sessions allowed',
                'validation_rules' => ['required', 'integer', 'min:1', 'max:50'],
                'sort_order' => 3,
            ],
            [
                'key' => 'whatsapp.auto_reconnect',
                'label' => 'Auto Reconnect Sessions',
                'value' => true,
                'type' => 'boolean',
                'group' => 'whatsapp',
                'description' => 'Automatically reconnect WhatsApp sessions when disconnected',
                'sort_order' => 4,
            ],
            
            // API Settings
            [
                'key' => 'api.rate_limit_per_minute',
                'label' => 'API Rate Limit (per minute)',
                'value' => 60,
                'type' => 'integer',
                'group' => 'api',
                'description' => 'Number of API requests allowed per minute per IP',
                'validation_rules' => ['required', 'integer', 'min:1'],
                'sort_order' => 1,
            ],
            [
                'key' => 'api.enable_cors',
                'label' => 'Enable CORS',
                'value' => false,
                'type' => 'boolean',
                'group' => 'api',
                'description' => 'Enable Cross-Origin Resource Sharing for API endpoints',
                'requires_restart' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'api.allowed_origins',
                'label' => 'Allowed Origins',
                'value' => ['http://localhost:3000', 'http://localhost:8080'],
                'type' => 'json',
                'group' => 'api',
                'description' => 'List of allowed origins for CORS requests',
                'sort_order' => 3,
            ],
            
            // Security Settings
            [
                'key' => 'security.password_min_length',
                'label' => 'Minimum Password Length',
                'value' => 8,
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Minimum required length for user passwords',
                'validation_rules' => ['required', 'integer', 'min:6', 'max:32'],
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'security.require_password_complexity',
                'label' => 'Require Password Complexity',
                'value' => true,
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Require passwords to contain uppercase, lowercase, numbers, and symbols',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'security.session_timeout_minutes',
                'label' => 'Session Timeout (minutes)',
                'value' => 480,
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Automatic session timeout in minutes (0 = no timeout)',
                'validation_rules' => ['required', 'integer', 'min:0'],
                'sort_order' => 3,
            ],
            [
                'key' => 'security.max_login_attempts',
                'label' => 'Max Login Attempts',
                'value' => 5,
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Maximum failed login attempts before account lockout',
                'validation_rules' => ['required', 'integer', 'min:1', 'max:10'],
                'sort_order' => 4,
            ],
            [
                'key' => 'security.lockout_duration_minutes',
                'label' => 'Account Lockout Duration (minutes)',
                'value' => 30,
                'type' => 'integer',
                'group' => 'security',
                'description' => 'How long accounts remain locked after max login attempts',
                'validation_rules' => ['required', 'integer', 'min:1'],
                'sort_order' => 5,
            ],
            
            // Integrations Settings
            [
                'key' => 'integrations.google_sheets_enabled',
                'label' => 'Google Sheets Integration',
                'value' => true,
                'type' => 'boolean',
                'group' => 'integrations',
                'description' => 'Enable Google Sheets integration for data sync',
                'sort_order' => 1,
            ],
            [
                'key' => 'integrations.webhook_timeout_seconds',
                'label' => 'Webhook Timeout (seconds)',
                'value' => 30,
                'type' => 'integer',
                'group' => 'integrations',
                'description' => 'Timeout for outgoing webhook requests',
                'validation_rules' => ['required', 'integer', 'min:5', 'max:120'],
                'sort_order' => 2,
            ],
            [
                'key' => 'integrations.webhook_retry_attempts',
                'label' => 'Webhook Retry Attempts',
                'value' => 3,
                'type' => 'integer',
                'group' => 'integrations',
                'description' => 'Number of retry attempts for failed webhooks',
                'validation_rules' => ['required', 'integer', 'min:0', 'max:10'],
                'sort_order' => 3,
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::create(array_merge($setting, [
                'created_by' => $userId,
                'updated_by' => $userId,
            ]));
        }

        $this->command->info('Created ' . count($settings) . ' system settings');
    }
}
