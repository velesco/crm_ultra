<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactSegmentController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataImportController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GoogleSheetsController;
use App\Http\Controllers\GmailOAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

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
Route::get('/email/track/open/{tracking_id}', [App\Http\Controllers\EmailCampaignController::class, 'trackOpen'])->name('email.track.open');
Route::get('/email/track/click/{tracking_id}', [App\Http\Controllers\EmailCampaignController::class, 'trackClick'])->name('email.track.click');
Route::get('/email/unsubscribe/{tracking_id}', [App\Http\Controllers\EmailCampaignController::class, 'unsubscribe'])->name('email.unsubscribe');

// SMS webhooks (public)
Route::post('/webhook/sms/twilio', [App\Http\Controllers\SmsController::class, 'twilioWebhook'])->name('sms.webhook.twilio');
Route::post('/webhook/sms/vonage', [App\Http\Controllers\SmsController::class, 'vonageWebhook'])->name('sms.webhook.vonage');
Route::post('/webhook/sms/orange', [App\Http\Controllers\SmsController::class, 'orangeWebhook'])->name('sms.webhook.orange');

// Authentication routes (provided by Laravel Breeze)
require __DIR__.'/auth.php';

// Google Login routes
Route::get('/auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {

    // CRITICAL FIX: Explicit contacts.import route definition (must be first)
    Route::get('/contacts/import', [ContactController::class, 'import'])->name('contacts.import');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
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
        Route::post('/bulk-actions', [ContactController::class, 'bulkActions'])->name('bulk-actions');
        Route::get('/export', [ContactController::class, 'export'])->name('export');
        // Route::get('/import', [ContactController::class, 'import'])->name('import'); // REMOVED - defined above
        Route::post('/import', [ContactController::class, 'processImport'])->name('import.process');
        Route::get('/import/{import}/status', [ContactController::class, 'importStatus'])->name('import.status');
        Route::post('/{contact}/note', [ContactController::class, 'addNote'])->name('add-note');
        Route::post('/{contact}/email', [ContactController::class, 'sendQuickEmail'])->name('quick-email');
        Route::post('/{contact}/sms', [ContactController::class, 'sendQuickSms'])->name('quick-sms');
        Route::post('/{contact}/whatsapp', [ContactController::class, 'sendQuickWhatsApp'])->name('quick-whatsapp');
    });

    // Additional fallback route for contacts import (in case of URL issues)
    Route::get('/contacts/import/', [ContactController::class, 'import'])->name('contacts.import.fallback');

    // Contact Segments
    Route::resource('segments', ContactSegmentController::class);
    Route::prefix('segments')->name('segments.')->group(function () {
        Route::post('/{segment}/refresh', [ContactSegmentController::class, 'refresh'])->name('refresh');
        Route::get('/{segment}/preview', [ContactSegmentController::class, 'preview'])->name('preview');
    });

    // Test route for SMTP debugging
    Route::get('/smtp-test', function () {
        return view('email.smtp.test.simple-form');
    })->name('smtp.test');

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
        Route::get('/api/spreadsheet/{spreadsheet_id}/info', [GoogleSheetsController::class, 'getSpreadsheetInfo'])->name('api.spreadsheet.info');
        Route::get('/api/spreadsheet/{spreadsheet_id}/sheets', [GoogleSheetsController::class, 'getSheets'])->name('api.spreadsheet.sheets');
        Route::get('/api/spreadsheet/{spreadsheet_id}/headers', [GoogleSheetsController::class, 'getHeaders'])->name('api.spreadsheet.headers');
    });

    // Communications (unified inbox)
    Route::prefix('communications')->name('communications.')->group(function () {
        Route::get('/', [CommunicationController::class, 'index'])->name('index');
        Route::post('/send-quick', [CommunicationController::class, 'sendQuick'])->name('sendQuick');
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
        Route::post('/export', [ReportController::class, 'export'])->name('export');
    });

    // Profile & Settings
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/update', [ProfileController::class, 'update'])->name('update');
        Route::delete('/delete', [ProfileController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
        Route::get('/security', [SettingsController::class, 'security'])->name('security');
        Route::get('/preferences', [SettingsController::class, 'preferences'])->name('preferences');
        Route::get('/integrations', [SettingsController::class, 'integrations'])->name('integrations');
        Route::get('/billing', [SettingsController::class, 'billing'])->name('billing');
        Route::get('/support', [SettingsController::class, 'support'])->name('support');
        Route::post('/update', [SettingsController::class, 'update'])->name('update');
        Route::post('/security/password', [SettingsController::class, 'updatePassword'])->name('security.password');
        Route::post('/notifications/update', [SettingsController::class, 'updateNotifications'])->name('notifications.update');

        // Profile Avatar Upload
        Route::post('/profile/avatar', [SettingsController::class, 'updateAvatar'])->name('profile.avatar');

        // Two-Factor Authentication (placeholder routes - to be implemented later)
        Route::post('/security/two-factor/enable', [SettingsController::class, 'enableTwoFactor'])->name('security.two-factor.enable');
        Route::post('/security/two-factor/disable', [SettingsController::class, 'disableTwoFactor'])->name('security.two-factor.disable');
    });

    // Gmail Integration Routes - UNIFIED SECTION
    Route::prefix('gmail')->name('gmail.')->group(function () {
        Route::get('/inbox', [App\Http\Controllers\GmailInboxController::class, 'index'])->name('inbox');
        Route::get('/oauth', [App\Http\Controllers\GmailOAuthController::class, 'index'])->name('oauth.index');
        Route::get('/oauth/connect', [App\Http\Controllers\GmailOAuthController::class, 'redirectToGoogle'])->name('oauth.connect');
        Route::get('/oauth/callback', [App\Http\Controllers\GmailOAuthController::class, 'handleCallback'])->name('oauth.callback');
        Route::delete('/oauth/{googleAccount}', [App\Http\Controllers\GmailOAuthController::class, 'disconnect'])->name('oauth.disconnect');
        Route::post('/oauth/{googleAccount}/refresh', [App\Http\Controllers\GmailOAuthController::class, 'refreshToken'])->name('oauth.refresh');
        Route::get('/oauth/{googleAccount}/status', [App\Http\Controllers\GmailOAuthController::class, 'status'])->name('oauth.status');
        Route::patch('/oauth/{googleAccount}/sync-settings', [App\Http\Controllers\GmailOAuthController::class, 'updateSyncSettings'])->name('oauth.sync-settings');
    });

    // Export Management (Accessible to all authenticated users)
    Route::resource('exports', ExportController::class);
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::post('/{export}/start', [ExportController::class, 'start'])->name('start');
        Route::post('/{export}/cancel', [ExportController::class, 'cancel'])->name('cancel');
        Route::post('/{export}/duplicate', [ExportController::class, 'duplicate'])->name('duplicate');
        Route::get('/{export}/download', [ExportController::class, 'download'])->name('download');
        Route::get('/{export}/progress', [ExportController::class, 'progress'])->name('progress');
        Route::get('/scheduled/index', [ExportController::class, 'scheduled'])->name('scheduled');
        Route::post('/bulk-action', [ExportController::class, 'bulk'])->name('bulk');
        Route::get('/columns/{dataType}', [ExportController::class, 'columns'])->name('columns');
        Route::get('/stats/data', [ExportController::class, 'stats'])->name('stats');
    });



    /*
    |--------------------------------------------------------------------------
    | Module Routes - Organized by Feature
    |--------------------------------------------------------------------------
    */

    // Email Management Module
    require __DIR__.'/modules/email.php';

    // SMS Management Module
    require __DIR__.'/modules/sms.php';

    // WhatsApp Management Module
    require __DIR__.'/modules/whatsapp.php';

    // Admin Panel Module
    require __DIR__.'/modules/admin.php';

    // API Routes for AJAX requests
    Route::prefix('api')->name('api.')->group(function () {
        // Search endpoints
        Route::get('/search/contacts', [ContactController::class, 'searchContacts'])->name('search.contacts');
        Route::get('/search/templates', [App\Http\Controllers\EmailTemplateController::class, 'searchTemplates'])->name('search.templates');

        // Quick stats
        Route::get('/stats/dashboard', [DashboardController::class, 'getDashboardStats'])->name('stats.dashboard');
        Route::get('/stats/contacts', [ContactController::class, 'getContactStats'])->name('stats.contacts');
        Route::get('/stats/email', [App\Http\Controllers\EmailCampaignController::class, 'getEmailStats'])->name('stats.email');
        Route::get('/stats/sms', [App\Http\Controllers\SmsController::class, 'getSmsStats'])->name('stats.sms');
        Route::get('/stats/whatsapp', [App\Http\Controllers\WhatsAppController::class, 'getWhatsAppStats'])->name('stats.whatsapp');

        // Real-time data
        Route::get('/live/whatsapp-messages', [App\Http\Controllers\WhatsAppController::class, 'getLiveMessages'])->name('live.whatsapp.messages');
        Route::get('/live/campaign-progress/{campaign}', [App\Http\Controllers\EmailCampaignController::class, 'getCampaignProgress'])->name('live.campaign.progress');

        // Form helpers
        Route::get('/contacts', [ContactController::class, 'searchContacts'])->name('contacts');
        Route::get('/contact-segments', [ContactSegmentController::class, 'getSegments'])->name('contact-segments');
        Route::get('/smtp-configs', [App\Http\Controllers\SmtpConfigController::class, 'getConfigs'])->name('smtp-configs');
        Route::get('/sms-providers', [App\Http\Controllers\SmsProviderController::class, 'getProviders'])->name('sms-providers');
        Route::get('/whatsapp-sessions', [App\Http\Controllers\WhatsAppSessionController::class, 'getSessions'])->name('whatsapp-sessions');

        // Gmail API endpoints
        Route::prefix('gmail')->name('gmail.')->group(function () {
            Route::get('/{email}', [App\Http\Controllers\GmailInboxController::class, 'show'])->name('show');
            Route::post('/mark-read', [App\Http\Controllers\GmailInboxController::class, 'markAsRead'])->name('mark-read');
            Route::post('/star', [App\Http\Controllers\GmailInboxController::class, 'starEmails'])->name('star');
            Route::post('/{email}/toggle-star', [App\Http\Controllers\GmailInboxController::class, 'toggleStar'])->name('toggle-star');
            Route::post('/{email}/mark-read', [App\Http\Controllers\GmailInboxController::class, 'markEmailAsRead'])->name('mark-email-read');
            Route::post('/sync-all', [App\Http\Controllers\GmailInboxController::class, 'syncAll'])->name('sync-all');

            // Team management endpoints
            Route::prefix('team')->name('team.')->group(function () {
                Route::get('/stats', [App\Http\Controllers\GmailTeamController::class, 'getTeamStats'])->name('stats');
                Route::post('/settings', [App\Http\Controllers\GmailTeamController::class, 'updateTeamSyncSettings'])->name('settings.update');
                Route::get('/export-settings', [App\Http\Controllers\GmailTeamController::class, 'exportTeamSettings'])->name('export-settings');
                Route::post('/{googleAccount}/visibility', [App\Http\Controllers\GmailTeamController::class, 'updateVisibility'])->name('visibility');
                Route::post('/{googleAccount}/grant-access', [App\Http\Controllers\GmailTeamController::class, 'grantAccess'])->name('grant-access');
                Route::delete('/{googleAccount}/revoke-access/{user}', [App\Http\Controllers\GmailTeamController::class, 'revokeAccess'])->name('revoke-access');
            });
        });
    });

    // Google Sheets Integration Routes
    Route::prefix('google-sheets')->name('google-sheets.')->group(function () {
        Route::get('/', [GoogleSheetsController::class, 'index'])->name('index');
        Route::get('/connect', [GoogleSheetsController::class, 'connect'])->name('connect');
        Route::get('/callback', [GoogleSheetsController::class, 'callback'])->name('callback');
        Route::post('/import', [GoogleSheetsController::class, 'import'])->name('import');
        Route::get('/sheets/{googleAccount}', [GoogleSheetsController::class, 'getSheets'])->name('sheets');
        Route::get('/columns/{googleAccount}/{sheetId}', [GoogleSheetsController::class, 'getColumns'])->name('columns');
        Route::post('/preview', [GoogleSheetsController::class, 'preview'])->name('preview');
        Route::get('/logs', [GoogleSheetsController::class, 'logs'])->name('logs');
        Route::delete('/disconnect/{googleAccount}', [GoogleSheetsController::class, 'disconnect'])->name('disconnect');
    });

    // Settings with Google Integration
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/google', [SettingsController::class, 'google'])->name('google.index');
        Route::get('/google/gmail-settings', [SettingsController::class, 'gmailSettings'])->name('google.gmail-settings');
        Route::get('/google/sheets-settings', [SettingsController::class, 'sheetsSettings'])->name('google.sheets-settings');
    });
});

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
    ]);
})->name('health');

// Test Horizon status
Route::get('/horizon-test', function () {
    return response()->json([
        'horizon_installed' => class_exists('Laravel\\Horizon\\Horizon'),
        'horizon_config' => config('horizon.path'),
        'queue_connection' => config('queue.default'),
        'redis_config' => config('database.redis.default'),
    ]);
})->name('horizon.test');
