<?php

use App\Http\Controllers\ProfileController;
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

// WhatsApp webhook (public)
Route::post('/webhook/whatsapp/{session?}', [WhatsAppController::class, 'webhook'])->name('whatsapp.webhook');

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
    Route::get('/api/dashboard/recent-activity', [DashboardController::class, 'getRecentActivity'])->name('dashboard.recent-activity');
    Route::get('/api/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/api/dashboard/quick-actions', [DashboardController::class, 'quickActions'])->name('dashboard.quick-actions');

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
    ]);

    Route::prefix('email-templates')->name('email.templates.')->group(function () {
        Route::get('/{template}/preview', [EmailTemplateController::class, 'preview'])->name('preview');
        Route::post('/{template}/duplicate', [EmailTemplateController::class, 'duplicate'])->name('duplicate');
    });

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
