<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\WebhookLogController;
use App\Http\Controllers\Admin\QueueMonitorController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\PerformanceController;
use App\Http\Controllers\Admin\AnalyticsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EmailCampaignController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\SmtpConfigController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WhatsAppSessionController;
use App\Http\Controllers\ContactSegmentController;
use App\Http\Controllers\SmsProviderController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DataImportController;
use App\Http\Controllers\GoogleSheetsController;
use App\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Email tracking routes (public, no auth required)
Route::get('/email/track/open/{tracking_id}', [EmailCampaignController::class, 'trackOpen'])->name('email.track.open');
Route::get('/email/track/click/{tracking_id}', [EmailCampaignController::class, 'trackClick'])->name('email.track.click');
Route::get('/email/unsubscribe/{tracking_id}', [EmailCampaignController::class, 'unsubscribe'])->name('email.unsubscribe');

// WhatsApp webhook (public) - Uses API route: /api/whatsapp/webhook

// SMS webhooks (public)
Route::post('/webhook/sms/twilio', [SmsController::class, 'twilioWebhook'])->name('sms.webhook.twilio');
Route::post('/webhook/sms/vonage', [SmsController::class, 'vonageWebhook'])->name('sms.webhook.vonage');
Route::post('/webhook/sms/orange', [SmsController::class, 'orangeWebhook'])->name('sms.webhook.orange');

// Authentication routes (provided by Laravel Breeze)
require __DIR__.'/auth.php';

// Google Login routes
Route::get('/auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard API endpoints
    Route::prefix('api/dashboard')->name('dashboard.')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats'])->name('stats');
        Route::get('/recent-activity', [DashboardController::class, 'getRecentActivity'])->name('recent-activity');
        Route::get('/system-status', [DashboardController::class, 'getSystemStatus'])->name('system-status');
        Route::get('/chart-data', [DashboardController::class, 'getChartData'])->name('chart-data');
        Route::get('/quick-actions', [DashboardController::class, 'quickActions'])->name('quick-actions');
        Route::get('/stream', [DashboardController::class, 'streamUpdates'])->name('stream');
    });

    // Contacts Management
    Route::resource('contacts', ContactController::class);
    Route::prefix('contacts')->name('contacts.')->group(function () {
        // Bulk actions
        Route::post('/bulk-actions', [ContactController::class, 'bulkActions'])->name('bulk-actions');

        // Export/Import
        Route::get('/export', [ContactController::class, 'export'])->name('export');
        Route::get('/import', [ContactController::class, 'import'])->name('import');
        Route::post('/import', [ContactController::class, 'processImport'])->name('import.process');
        Route::get('/import/{import}/status', [ContactController::class, 'importStatus'])->name('import.status');

        // Quick actions for individual contacts
        Route::post('/{contact}/note', [ContactController::class, 'addNote'])->name('add-note');
        Route::post('/{contact}/email', [ContactController::class, 'sendQuickEmail'])->name('quick-email');
        Route::post('/{contact}/sms', [ContactController::class, 'sendQuickSms'])->name('quick-sms');
        Route::post('/{contact}/whatsapp', [ContactController::class, 'sendQuickWhatsApp'])->name('quick-whatsapp');
    });

    // Contact Segments
    Route::resource('segments', ContactSegmentController::class)->except(['show']);
    Route::prefix('segments')->name('segments.')->group(function () {
        Route::post('/{segment}/refresh', [ContactSegmentController::class, 'refresh'])->name('refresh');
        Route::get('/{segment}/preview', [ContactSegmentController::class, 'preview'])->name('preview');
    });

    // Email Campaigns
    Route::resource('email-campaigns', EmailCampaignController::class)->names([
        'index' => 'email.campaigns.index',
        'create' => 'email.campaigns.create',
        'store' => 'email.campaigns.store',
        'show' => 'email.campaigns.show',
        'edit' => 'email.campaigns.edit',
        'update' => 'email.campaigns.update',
        'destroy' => 'email.campaigns.destroy'
    ]);

    Route::prefix('email-campaigns')->name('email.campaigns.')->group(function () {
        // Campaign actions
        Route::post('/{campaign}/send', [EmailCampaignController::class, 'send'])->name('send');
        Route::post('/{campaign}/pause', [EmailCampaignController::class, 'pause'])->name('pause');
        Route::post('/{campaign}/resume', [EmailCampaignController::class, 'resume'])->name('resume');
        Route::post('/{campaign}/schedule', [EmailCampaignController::class, 'schedule'])->name('schedule');
        Route::post('/{campaign}/duplicate', [EmailCampaignController::class, 'duplicate'])->name('duplicate');
        Route::post('/{campaign}/test', [EmailCampaignController::class, 'sendTest'])->name('test');

        // Campaign management
        Route::post('/{campaign}/contacts', [EmailCampaignController::class, 'addContacts'])->name('add-contacts');
        Route::delete('/{campaign}/contacts', [EmailCampaignController::class, 'removeContacts'])->name('remove-contacts');
        Route::get('/{campaign}/preview', [EmailCampaignController::class, 'preview'])->name('preview');
        Route::get('/{campaign}/stats', [EmailCampaignController::class, 'stats'])->name('stats');

        // Campaign reports
        Route::get('/{campaign}/report', [EmailCampaignController::class, 'report'])->name('report');
        Route::get('/{campaign}/export-report', [EmailCampaignController::class, 'exportReport'])->name('export-report');
    });

    // Email Templates
    Route::resource('email-templates', EmailTemplateController::class)->names([
        'index' => 'email.templates.index',
        'create' => 'email.templates.create',
        'store' => 'email.templates.store',
        'show' => 'email.templates.show',
        'edit' => 'email.templates.edit',
        'update' => 'email.templates.update',
        'destroy' => 'email.templates.destroy'
    ])->parameters(['email-templates' => 'email_template']);

    Route::prefix('email-templates')->name('email.templates.')->group(function () {
        Route::get('/{email_template}/preview', [EmailTemplateController::class, 'preview'])->name('preview');
        Route::post('/{email_template}/duplicate', [EmailTemplateController::class, 'duplicate'])->name('duplicate');
    });

    // Test route for SMTP debugging
    Route::get('/smtp-test', function () {
        return view('email.smtp.test.simple-form');
    })->name('smtp.test');

    // SMTP Configurations
    Route::resource('smtp-configs', SmtpConfigController::class)->names([
        'index' => 'smtp-configs.index',
        'create' => 'smtp-configs.create',
        'store' => 'smtp-configs.store',
        'show' => 'smtp-configs.show',
        'edit' => 'smtp-configs.edit',
        'update' => 'smtp-configs.update',
        'destroy' => 'smtp-configs.destroy'
    ]);

    Route::prefix('smtp-configs')->name('smtp-configs.')->group(function () {
        Route::post('/{smtpConfig}/test', [SmtpConfigController::class, 'test'])->name('test');
        Route::patch('/{smtpConfig}/toggle', [SmtpConfigController::class, 'toggle'])->name('toggle');
        Route::post('/{smtpConfig}/duplicate', [SmtpConfigController::class, 'duplicate'])->name('duplicate');
        Route::post('/{smtpConfig}/reset-counters', [SmtpConfigController::class, 'resetCounters'])->name('reset-counters');
        Route::get('/providers', [SmtpConfigController::class, 'getProviders'])->name('providers');
        Route::get('/provider-settings/{provider}', [SmtpConfigController::class, 'getProviderSettings'])->name('provider-settings');
    });

    // SMS Management
    Route::resource('sms', SmsController::class);
    Route::prefix('sms')->name('sms.')->group(function () {
        Route::post('/send-to-segment', [SmsController::class, 'sendToSegment'])->name('send-to-segment');
        Route::get('/statistics', [SmsController::class, 'statistics'])->name('statistics');
        Route::post('/{smsMessage}/cancel', [SmsController::class, 'cancel'])->name('cancel');
        Route::post('/{smsMessage}/resend', [SmsController::class, 'resend'])->name('resend');
    });

    // SMS Providers
    Route::resource('sms-providers', SmsProviderController::class)->names([
        'index' => 'sms.providers.index',
        'create' => 'sms.providers.create',
        'store' => 'sms.providers.store',
        'show' => 'sms.providers.show',
        'edit' => 'sms.providers.edit',
        'update' => 'sms.providers.update',
        'destroy' => 'sms.providers.destroy'
    ]);

    Route::prefix('sms-providers')->name('sms.providers.')->group(function () {
        Route::post('/{provider}/test', [SmsProviderController::class, 'test'])->name('test');
        Route::post('/{provider}/toggle-active', [SmsProviderController::class, 'toggleActive'])->name('toggle-active');
    });

    // WhatsApp Management
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/', [WhatsAppController::class, 'index'])->name('index');
        Route::get('/chat', [WhatsAppController::class, 'chat'])->name('chat');
        Route::get('/chat/{contact}', [WhatsAppController::class, 'chatWithContact'])->name('chat.contact');
        Route::post('/send-message', [WhatsAppController::class, 'sendMessage'])->name('send-message');
        Route::get('/contacts', [WhatsAppController::class, 'contacts'])->name('contacts');
        Route::get('/history', [WhatsAppController::class, 'history'])->name('history');
    });

    // WhatsApp Sessions
    Route::resource('whatsapp-sessions', WhatsAppSessionController::class)->names([
        'index' => 'whatsapp.sessions.index',
        'create' => 'whatsapp.sessions.create',
        'store' => 'whatsapp.sessions.store',
        'show' => 'whatsapp.sessions.show',
        'edit' => 'whatsapp.sessions.edit',
        'update' => 'whatsapp.sessions.update',
        'destroy' => 'whatsapp.sessions.destroy'
    ]);

    Route::prefix('whatsapp-sessions')->name('whatsapp.sessions.')->group(function () {
        Route::post('/{session}/start', [WhatsAppSessionController::class, 'start'])->name('start');
        Route::post('/{session}/stop', [WhatsAppSessionController::class, 'stop'])->name('stop');
        Route::get('/{session}/qr', [WhatsAppSessionController::class, 'getQR'])->name('qr');
        Route::get('/{session}/status', [WhatsAppSessionController::class, 'getStatus'])->name('status');
    });

    // Data Import & Export
    Route::prefix('data')->name('data.')->group(function () {
        Route::get('/import', [DataImportController::class, 'index'])->name('import.index');
        Route::get('/import/create', [DataImportController::class, 'create'])->name('import.create');
        Route::post('/import/upload', [DataImportController::class, 'upload'])->name('import.upload');
        Route::post('/import/preview', [DataImportController::class, 'preview'])->name('import.preview');
        Route::post('/import/process', [DataImportController::class, 'process'])->name('import.process');
        Route::get('/import/{import}', [DataImportController::class, 'show'])->name('import.show');
        Route::get('/import/{import}/download-errors', [DataImportController::class, 'downloadErrors'])->name('import.download-errors');

        Route::get('/export', [DataImportController::class, 'exportIndex'])->name('export.index');
        Route::post('/export/contacts', [DataImportController::class, 'exportContacts'])->name('export.contacts');
        Route::post('/export/communications', [DataImportController::class, 'exportCommunications'])->name('export.communications');
    });

    // Google Sheets Integration
    Route::prefix('google-sheets')->name('google.sheets.')->group(function () {
        Route::get('/', [GoogleSheetsController::class, 'index'])->name('index');
        Route::get('/auth', [GoogleSheetsController::class, 'authenticate'])->name('auth');
        Route::get('/callback', [GoogleSheetsController::class, 'callback'])->name('callback');

        Route::get('/create', [GoogleSheetsController::class, 'create'])->name('create');
        Route::post('/store', [GoogleSheetsController::class, 'store'])->name('store');
        Route::get('/{integration}', [GoogleSheetsController::class, 'show'])->name('show');
        Route::get('/{integration}/edit', [GoogleSheetsController::class, 'edit'])->name('edit');
        Route::put('/{integration}', [GoogleSheetsController::class, 'update'])->name('update');
        Route::delete('/{integration}', [GoogleSheetsController::class, 'destroy'])->name('destroy');

        Route::post('/{integration}/sync', [GoogleSheetsController::class, 'sync'])->name('sync');
        Route::post('/{integration}/test', [GoogleSheetsController::class, 'test'])->name('test');
        Route::get('/{integration}/preview', [GoogleSheetsController::class, 'preview'])->name('preview');

        // AJAX endpoints for sheet info
        Route::get('/api/spreadsheet/{spreadsheet_id}/info', [GoogleSheetsController::class, 'getSpreadsheetInfo'])->name('api.spreadsheet.info');
        Route::get('/api/spreadsheet/{spreadsheet_id}/sheets', [GoogleSheetsController::class, 'getSheets'])->name('api.spreadsheet.sheets');
        Route::get('/api/spreadsheet/{spreadsheet_id}/headers', [GoogleSheetsController::class, 'getHeaders'])->name('api.spreadsheet.headers');
    });

    // Communications (unified inbox)
    Route::prefix('communications')->name('communications.')->group(function () {
        Route::get('/', [CommunicationController::class, 'index'])->name('index');
        Route::post('/send', [CommunicationController::class, 'sendQuick'])->name('send');
        Route::get('/{contact}/conversation', [CommunicationController::class, 'conversation'])->name('conversation');
        Route::get('/inbox', [CommunicationController::class, 'inbox'])->name('inbox');
        Route::get('/sent', [CommunicationController::class, 'sent'])->name('sent');
        Route::get('/scheduled', [CommunicationController::class, 'scheduled'])->name('scheduled');
        Route::get('/{communication}', [CommunicationController::class, 'show'])->name('show');
        Route::post('/{communication}/reply', [CommunicationController::class, 'reply'])->name('reply');
        Route::post('/{communication}/mark-read', [CommunicationController::class, 'markAsRead'])->name('mark-read');
        Route::delete('/{communication}', [CommunicationController::class, 'destroy'])->name('destroy');
    });

    // Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/contacts', [ReportController::class, 'contacts'])->name('contacts');
        Route::get('/email-campaigns', [ReportController::class, 'emailCampaigns'])->name('email-campaigns');
        Route::get('/sms', [ReportController::class, 'sms'])->name('sms');
        Route::get('/whatsapp', [ReportController::class, 'whatsapp'])->name('whatsapp');
        Route::get('/communications', [ReportController::class, 'communications'])->name('communications');

        // Export reports
        Route::post('/export', [ReportController::class, 'export'])->name('export');
    });

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        // General settings
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/general', [SettingsController::class, 'general'])->name('general');
        Route::post('/general', [SettingsController::class, 'updateGeneral'])->name('general.update');

        // User profile
        Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
        Route::post('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/avatar', [SettingsController::class, 'updateAvatar'])->name('profile.avatar');
        Route::delete('/profile/avatar', [SettingsController::class, 'deleteAvatar'])->name('profile.avatar.delete');

        // Security
        Route::get('/security', [SettingsController::class, 'security'])->name('security');
        Route::post('/security/password', [SettingsController::class, 'updatePassword'])->name('security.password');
        Route::post('/security/two-factor', [SettingsController::class, 'enableTwoFactor'])->name('security.two-factor.enable');
        Route::delete('/security/two-factor', [SettingsController::class, 'disableTwoFactor'])->name('security.two-factor.disable');

        // Notifications
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
        Route::post('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications.update');

        // Integrations
        Route::get('/integrations', [SettingsController::class, 'integrations'])->name('integrations');

        // API Keys
        Route::get('/api-keys', [SettingsController::class, 'apiKeys'])->name('api-keys');
        Route::post('/api-keys', [SettingsController::class, 'createApiKey'])->name('api-keys.create');
        Route::delete('/api-keys/{key}', [SettingsController::class, 'deleteApiKey'])->name('api-keys.delete');

        // Team management (if multi-user)
        Route::get('/team', [SettingsController::class, 'team'])->name('team');
        Route::post('/team/invite', [SettingsController::class, 'inviteTeamMember'])->name('team.invite');
        Route::delete('/team/{user}', [SettingsController::class, 'removeTeamMember'])->name('team.remove');
        Route::post('/team/{user}/permissions', [SettingsController::class, 'updatePermissions'])->name('team.permissions');
    });

    // Admin routes - Restricted to super_admin and admin roles
    Route::prefix('admin')->name('admin.')->middleware(['role:super_admin|admin'])->group(function () {
        // Main admin dashboard
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        
        // User Management
        Route::resource('user-management', UserManagementController::class)->names('user-management');
        Route::post('/user-management/bulk-action', [UserManagementController::class, 'bulkAction'])->name('user-management.bulk-action');
        Route::patch('/user-management/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('user-management.toggle-status');
        Route::get('/user-management/{user}/activity', [UserManagementController::class, 'getActivity'])->name('user-management.activity');
        Route::get('/user-management/export/csv', [UserManagementController::class, 'export'])->name('user-management.export');
        
        // System Logs Management
        Route::resource('system-logs', SystemLogController::class)->only(['index', 'show'])->names('system-logs');
        Route::post('/system-logs/clear-old', [SystemLogController::class, 'clearOld'])->name('system-logs.clear-old');
        Route::get('/system-logs/export', [SystemLogController::class, 'export'])->name('system-logs.export');
        Route::get('/system-logs/chart-data', [SystemLogController::class, 'chartData'])->name('system-logs.chart-data');
        Route::get('/system-logs/recent-activity', [SystemLogController::class, 'recentActivity'])->name('system-logs.recent-activity');
        Route::get('/system-logs/health-metrics', [SystemLogController::class, 'healthMetrics'])->name('system-logs.health-metrics');
        
        // Backup Management
        Route::resource('backups', BackupController::class)->except(['edit', 'update'])->names('backups');
        Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::post('/backups/scheduled', [BackupController::class, 'scheduled'])->name('backups.scheduled');
        Route::get('/backups/stats', [BackupController::class, 'stats'])->name('backups.stats');
        Route::post('/backups/{backup}/validate', [BackupController::class, 'validate'])->name('backups.validate');
        Route::post('/backups/cleanup', [BackupController::class, 'cleanup'])->name('backups.cleanup');
        Route::post('/backups/bulk-action', [BackupController::class, 'bulkAction'])->name('backups.bulk-action');
        
        // System management
        Route::get('/stats', [AdminController::class, 'getStats'])->name('stats');
        Route::get('/health', [AdminController::class, 'getHealthCheck'])->name('health');
        Route::get('/activity', [AdminController::class, 'getRecentActivity'])->name('activity');
        Route::get('/alerts', [AdminController::class, 'getSystemAlerts'])->name('alerts');
        Route::post('/alerts/dismiss', [AdminController::class, 'dismissAlert'])->name('alerts.dismiss');
        
        // System actions
        Route::post('/maintenance/toggle', [AdminController::class, 'toggleMaintenance'])->name('toggle-maintenance');
        Route::post('/caches/clear', [AdminController::class, 'clearCaches'])->name('clear-caches');
        Route::post('/optimize', [AdminController::class, 'optimize'])->name('optimize');
        Route::post('/export', [AdminController::class, 'exportSystemData'])->name('export-data');
        Route::get('/system-info', [AdminController::class, 'getSystemInfo'])->name('system-info');
        
        // System Settings Management
        Route::resource('settings', SystemSettingsController::class)->names('admin.settings');
        Route::post('/settings/bulk-action', [SystemSettingsController::class, 'bulkAction'])->name('settings.bulk-action');
        Route::get('/settings/export', [SystemSettingsController::class, 'export'])->name('settings.export');
        Route::post('/settings/clear-cache', [SystemSettingsController::class, 'clearCache'])->name('settings.clear-cache');
        
        // API Key Management
        Route::resource('api-keys', ApiKeyController::class)->names('api-keys');
        Route::post('/api-keys/{apiKey}/regenerate', [ApiKeyController::class, 'regenerate'])->name('api-keys.regenerate');
        Route::post('/api-keys/{apiKey}/toggle-status', [ApiKeyController::class, 'toggleStatus'])->name('api-keys.toggle-status');
        Route::post('/api-keys/bulk-action', [ApiKeyController::class, 'bulkAction'])->name('api-keys.bulk-action');
        Route::get('/api-keys/export', [ApiKeyController::class, 'export'])->name('api-keys.export');
        
        // Security Management
        Route::prefix('security')->name('security.')->group(function () {
            Route::get('/', [SecurityController::class, 'index'])->name('index');
            Route::get('/login-attempts', [SecurityController::class, 'loginAttempts'])->name('login-attempts');
            Route::post('/block-ip', [SecurityController::class, 'blockIp'])->name('block-ip');
            Route::post('/unblock-ip', [SecurityController::class, 'unblockIp'])->name('unblock-ip');
            Route::post('/block-user', [SecurityController::class, 'blockUser'])->name('block-user');
            Route::post('/unblock-user', [SecurityController::class, 'unblockUser'])->name('unblock-user');
            Route::get('/chart-data', [SecurityController::class, 'chartData'])->name('chart-data');
            Route::post('/clear-old', [SecurityController::class, 'clearOldAttempts'])->name('clear-old');
            Route::get('/export', [SecurityController::class, 'export'])->name('export');
        });
        
        // Webhook Logs Management
        Route::resource('webhook-logs', WebhookLogController::class)->only(['index', 'show'])->names('webhook-logs');
        Route::post('/webhook-logs/{webhookLog}/retry', [WebhookLogController::class, 'retry'])->name('webhook-logs.retry');
        Route::post('/webhook-logs/bulk-retry', [WebhookLogController::class, 'bulkRetry'])->name('webhook-logs.bulk-retry');
        Route::post('/webhook-logs/clear-old', [WebhookLogController::class, 'clearOld'])->name('webhook-logs.clear-old');
        Route::get('/webhook-logs/export', [WebhookLogController::class, 'export'])->name('webhook-logs.export');
        Route::get('/webhook-logs/chart-data', [WebhookLogController::class, 'chartData'])->name('webhook-logs.chart-data');
        Route::get('/webhook-logs/health-metrics', [WebhookLogController::class, 'healthMetrics'])->name('webhook-logs.health-metrics');
        Route::get('/webhook-logs/recent-activity', [WebhookLogController::class, 'recentActivity'])->name('webhook-logs.recent-activity');

        // Queue Monitor Routes
        Route::get('/queue-monitor', [QueueMonitorController::class, 'index'])->name('queue-monitor.index');
        Route::get('/queue-monitor/{id}', [QueueMonitorController::class, 'show'])->name('queue-monitor.show');
        Route::post('/queue-monitor/retry/{id}', [QueueMonitorController::class, 'retryJob'])->name('queue-monitor.retry');
        Route::delete('/queue-monitor/delete/{id}', [QueueMonitorController::class, 'deleteJob'])->name('queue-monitor.delete');
        Route::post('/queue-monitor/retry-all', [QueueMonitorController::class, 'retryAllFailed'])->name('queue-monitor.retry-all');
        Route::post('/queue-monitor/clear-all', [QueueMonitorController::class, 'clearAllFailed'])->name('queue-monitor.clear-all');
        Route::post('/queue-monitor/pause', [QueueMonitorController::class, 'pauseQueue'])->name('queue-monitor.pause');
        Route::post('/queue-monitor/resume', [QueueMonitorController::class, 'resumeQueue'])->name('queue-monitor.resume');
        Route::get('/queue-monitor/export', [QueueMonitorController::class, 'export'])->name('queue-monitor.export');
        Route::get('/queue-monitor/health', [QueueMonitorController::class, 'health'])->name('queue-monitor.health');
        
        // Performance Monitoring Routes
        Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');
        Route::get('/performance/metrics', [PerformanceController::class, 'show'])->name('performance.show');
        Route::get('/performance/system-metrics', [PerformanceController::class, 'getSystemMetrics'])->name('performance.system-metrics');
        Route::get('/performance/chart-data', [PerformanceController::class, 'getChartData'])->name('performance.chart-data');
        Route::get('/performance/stats', [PerformanceController::class, 'getStats'])->name('performance.stats');
        Route::delete('/performance/clean', [PerformanceController::class, 'cleanOldMetrics'])->name('performance.clean');
        Route::get('/performance/export', [PerformanceController::class, 'export'])->name('performance.export');
        
        // Business Intelligence & Analytics Management
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            Route::get('/revenue', [AnalyticsController::class, 'revenue'])->name('revenue');
            Route::get('/campaigns', [AnalyticsController::class, 'campaigns'])->name('campaigns');
            Route::get('/contacts', [AnalyticsController::class, 'contacts'])->name('contacts');
            Route::get('/performance', [AnalyticsController::class, 'performance'])->name('performance');
            Route::get('/realtime', [AnalyticsController::class, 'realtime'])->name('realtime');
            Route::get('/export', [AnalyticsController::class, 'export'])->name('export');
        });
    });

    // API Routes for AJAX requests
    Route::prefix('api')->name('api.')->group(function () {
        // Search endpoints
        Route::get('/search/contacts', [ContactController::class, 'searchContacts'])->name('search.contacts');
        Route::get('/search/templates', [EmailTemplateController::class, 'searchTemplates'])->name('search.templates');

        // Quick stats
        Route::get('/stats/dashboard', [DashboardController::class, 'getDashboardStats'])->name('stats.dashboard');
        Route::get('/stats/contacts', [ContactController::class, 'getContactStats'])->name('stats.contacts');
        Route::get('/stats/email', [EmailCampaignController::class, 'getEmailStats'])->name('stats.email');
        Route::get('/stats/sms', [SmsController::class, 'getSmsStats'])->name('stats.sms');
        Route::get('/stats/whatsapp', [WhatsAppController::class, 'getWhatsAppStats'])->name('stats.whatsapp');

        // Real-time data
        Route::get('/live/whatsapp-messages', [WhatsAppController::class, 'getLiveMessages'])->name('live.whatsapp.messages');
        Route::get('/live/campaign-progress/{campaign}', [EmailCampaignController::class, 'getCampaignProgress'])->name('live.campaign.progress');

        // Form helpers
        Route::get('/contacts', [ContactController::class, 'searchContacts'])->name('contacts');
        Route::get('/contact-segments', [ContactSegmentController::class, 'getSegments'])->name('contact-segments');
        Route::get('/smtp-configs', [SmtpConfigController::class, 'getConfigs'])->name('smtp-configs');
        Route::get('/sms-providers', [SmsProviderController::class, 'getProviders'])->name('sms-providers');
        Route::get('/whatsapp-sessions', [WhatsAppSessionController::class, 'getSessions'])->name('whatsapp-sessions');
    });
});

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0')
    ]);
})->name('health');

// Test Horizon status
Route::get('/horizon-test', function () {
    return response()->json([
        'horizon_installed' => class_exists('Laravel\\Horizon\\Horizon'),
        'horizon_config' => config('horizon.path'),
        'queue_connection' => config('queue.default'),
        'redis_config' => config('database.redis.default')
    ]);
})->name('horizon.test');
