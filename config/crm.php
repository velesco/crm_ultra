<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CRM Ultra Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings specific to CRM Ultra
    | application functionality.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Application Settings
    |--------------------------------------------------------------------------
    */

    'app' => [
        'name' => env('APP_NAME', 'CRM Ultra'),
        'version' => env('APP_VERSION', '1.0.0'),
        'timezone' => env('CRM_DEFAULT_TIMEZONE', 'Europe/Bucharest'),
        'language' => env('CRM_DEFAULT_LANGUAGE', 'en'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Limits and Quotas
    |--------------------------------------------------------------------------
    */

    'limits' => [
        'max_contacts_per_user' => env('CRM_MAX_CONTACTS_PER_USER', 10000),
        'max_emails_per_hour' => env('CRM_MAX_EMAILS_PER_HOUR', 1000),
        'max_sms_per_day' => env('CRM_MAX_SMS_PER_DAY', 500),
        'max_whatsapp_per_hour' => env('CRM_MAX_WHATSAPP_PER_HOUR', 200),
        'upload_max_size' => env('CRM_UPLOAD_MAX_SIZE', 10240), // KB
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    */

    'email' => [
        'tracking_enabled' => env('CRM_EMAIL_TRACKING_ENABLED', true),
        'unsubscribe_enabled' => env('CRM_EMAIL_UNSUBSCRIBE_ENABLED', true),
        'default_template' => env('CRM_EMAIL_DEFAULT_TEMPLATE', 'default'),
        'send_delay' => env('CRM_EMAIL_SEND_DELAY', 1), // seconds between emails
        'batch_size' => env('CRM_EMAIL_BATCH_SIZE', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    */

    'sms' => [
        'default_provider' => env('CRM_SMS_DEFAULT_PROVIDER', 'twilio'),
        'max_length' => env('CRM_SMS_MAX_LENGTH', 160),
        'allow_unicode' => env('CRM_SMS_ALLOW_UNICODE', true),
        'send_delay' => env('CRM_SMS_SEND_DELAY', 0.5), // seconds between SMS
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Configuration
    |--------------------------------------------------------------------------
    */

    'whatsapp' => [
        'api_url' => env('WHATSAPP_API_URL', 'http://localhost:3000'),
        'api_key' => env('WHATSAPP_API_KEY'),
        'webhook_url' => env('WHATSAPP_WEBHOOK_URL'),
        'session_timeout' => env('WHATSAPP_SESSION_TIMEOUT', 300), // seconds
        'max_message_length' => env('WHATSAPP_MAX_MESSAGE_LENGTH', 4096),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Services Configuration
    |--------------------------------------------------------------------------
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
        'scopes' => [
            'https://www.googleapis.com/auth/spreadsheets',
            'https://www.googleapis.com/auth/drive.readonly',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */

    'security' => [
        'two_factor_enabled' => env('SECURITY_TWO_FACTOR_ENABLED', false),
        'password_reset_timeout' => env('SECURITY_PASSWORD_RESET_TIMEOUT', 60), // minutes
        'session_timeout' => env('SECURITY_SESSION_TIMEOUT', 120), // minutes
        'max_login_attempts' => env('SECURITY_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('SECURITY_LOCKOUT_DURATION', 15), // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        'api' => env('RATE_LIMIT_API', 60), // requests per minute
        'email' => env('RATE_LIMIT_EMAIL', 100), // emails per hour
        'sms' => env('RATE_LIMIT_SMS', 50), // SMS per hour
        'whatsapp' => env('RATE_LIMIT_WHATSAPP', 200), // messages per hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Import/Export Settings
    |--------------------------------------------------------------------------
    */

    'import' => [
        'max_file_size' => env('CRM_IMPORT_MAX_FILE_SIZE', 10240), // KB
        'allowed_extensions' => ['csv', 'xlsx', 'xls'],
        'batch_size' => env('CRM_IMPORT_BATCH_SIZE', 100),
        'max_errors' => env('CRM_IMPORT_MAX_ERRORS', 50),
    ],

    'export' => [
        'max_records' => env('CRM_EXPORT_MAX_RECORDS', 10000),
        'formats' => ['csv', 'xlsx', 'pdf'],
        'default_format' => env('CRM_EXPORT_DEFAULT_FORMAT', 'csv'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    */

    'backup' => [
        'enabled' => env('BACKUP_ENABLED', true),
        'schedule' => env('BACKUP_SCHEDULE', '0 2 * * *'), // Daily at 2 AM
        'retention_days' => env('BACKUP_RETENTION_DAYS', 30),
        'include_uploads' => env('BACKUP_INCLUDE_UPLOADS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Settings
    |--------------------------------------------------------------------------
    */

    'contacts' => [
        'required_fields' => ['first_name'],
        'default_status' => 'active',
        'auto_assign' => env('CRM_CONTACTS_AUTO_ASSIGN', true),
        'duplicate_detection' => env('CRM_CONTACTS_DUPLICATE_DETECTION', true),
        'merge_duplicates' => env('CRM_CONTACTS_MERGE_DUPLICATES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Settings
    |--------------------------------------------------------------------------
    */

    'dashboard' => [
        'refresh_interval' => env('CRM_DASHBOARD_REFRESH_INTERVAL', 30), // seconds
        'chart_data_points' => env('CRM_DASHBOARD_CHART_DATA_POINTS', 30),
        'recent_activity_limit' => env('CRM_DASHBOARD_RECENT_ACTIVITY_LIMIT', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */

    'features' => [
        'email_campaigns' => env('CRM_FEATURE_EMAIL_CAMPAIGNS', true),
        'sms_messaging' => env('CRM_FEATURE_SMS_MESSAGING', true),
        'whatsapp_integration' => env('CRM_FEATURE_WHATSAPP_INTEGRATION', true),
        'google_sheets_sync' => env('CRM_FEATURE_GOOGLE_SHEETS_SYNC', true),
        'contact_segments' => env('CRM_FEATURE_CONTACT_SEGMENTS', true),
        'advanced_reporting' => env('CRM_FEATURE_ADVANCED_REPORTING', true),
        'team_collaboration' => env('CRM_FEATURE_TEAM_COLLABORATION', false),
        'api_access' => env('CRM_FEATURE_API_ACCESS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'email_enabled' => env('CRM_NOTIFICATIONS_EMAIL_ENABLED', true),
        'browser_enabled' => env('CRM_NOTIFICATIONS_BROWSER_ENABLED', true),
        'slack_enabled' => env('CRM_NOTIFICATIONS_SLACK_ENABLED', false),
        'slack_webhook' => env('CRM_NOTIFICATIONS_SLACK_WEBHOOK'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme and UI Settings
    |--------------------------------------------------------------------------
    */

    'ui' => [
        'theme' => env('CRM_UI_THEME', 'light'), // light, dark, auto
        'sidebar_collapsed' => env('CRM_UI_SIDEBAR_COLLAPSED', false),
        'items_per_page' => env('CRM_UI_ITEMS_PER_PAGE', 25),
        'date_format' => env('CRM_UI_DATE_FORMAT', 'Y-m-d'),
        'time_format' => env('CRM_UI_TIME_FORMAT', 'H:i:s'),
    ],

];
