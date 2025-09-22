<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\ComplianceController;
use App\Http\Controllers\Admin\PerformanceController;
use App\Http\Controllers\Admin\QueueMonitorController;
use App\Http\Controllers\Admin\RevenueController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Admin\WebhookLogController;
use App\Http\Controllers\CustomReportController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SystemSettingsController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

// Admin Panel - Requires super_admin or admin role
Route::prefix('admin')->name('admin.')->middleware(['role:super_admin|admin'])->group(function () {

    // Admin Dashboard
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('user-management', UserManagementController::class);
    Route::prefix('user-management')->name('user-management.')->group(function () {
        Route::post('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
        Route::post('/bulk-action', [UserManagementController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/activity/{user}', [UserManagementController::class, 'activity'])->name('activity');
        Route::get('/export', [UserManagementController::class, 'export'])->name('export');
    });

    // System Logs
    Route::resource('system-logs', SystemLogController::class)->only(['index', 'show'])->names('system-logs');
    Route::prefix('system-logs')->name('system-logs.')->group(function () {
        Route::get('/chart-data', [SystemLogController::class, 'chartData'])->name('chart-data');
        Route::get('/health-metrics', [SystemLogController::class, 'healthMetrics'])->name('health-metrics');
        Route::get('/recent-activity', [SystemLogController::class, 'recentActivity'])->name('recent-activity');
        Route::post('/clear-old', [SystemLogController::class, 'clearOldLogs'])->name('clear-old');
        Route::get('/export', [SystemLogController::class, 'export'])->name('export');
        Route::get('/related/{systemLog}', [SystemLogController::class, 'related'])->name('related');
        Route::get('/table', [SystemLogController::class, 'table'])->name('table');
    });

    // System Backups
    Route::resource('backups', BackupController::class)->names('backups');
    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/scheduled', [BackupController::class, 'scheduled'])->name('scheduled');
        Route::post('/cleanup', [BackupController::class, 'cleanup'])->name('cleanup');
        Route::post('/{backup}/restore', [BackupController::class, 'restore'])->name('restore');
        Route::post('/{backup}/download', [BackupController::class, 'download'])->name('download');
        Route::post('/{backup}/validate', [BackupController::class, 'validate'])->name('validate');
        Route::post('/bulk-action', [BackupController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/table', [BackupController::class, 'table'])->name('table');
    });

    // Admin API endpoints
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/stats', [AdminController::class, 'getStats'])->name('stats');
        Route::get('/alerts', [AdminController::class, 'getSystemAlerts'])->name('alerts');
        Route::post('/alerts/dismiss', [AdminController::class, 'dismissAlert'])->name('alerts.dismiss');
    });

    // System actions
    Route::post('/maintenance/toggle', [AdminController::class, 'toggleMaintenance'])->name('toggle-maintenance');
    Route::post('/caches/clear', [AdminController::class, 'clearCaches'])->name('clear-caches');
    Route::post('/optimize', [AdminController::class, 'optimize'])->name('optimize');
    Route::post('/export', [AdminController::class, 'exportSystemData'])->name('export-data');
    Route::get('/system-info', [AdminController::class, 'getSystemInfo'])->name('system-info');

    // System Settings Management
    Route::resource('settings', SystemSettingsController::class)->names('settings');
    Route::get('settings/general', [SettingsController::class, 'general'])->name('settings.general');
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

    // Revenue Management & Financial Analytics
    Route::prefix('revenue')->name('revenue.')->group(function () {
        Route::get('/', [RevenueController::class, 'index'])->name('index');
        Route::get('/monthly', [RevenueController::class, 'monthly'])->name('monthly');
        Route::get('/customers', [RevenueController::class, 'customers'])->name('customers');
        Route::get('/forecast', [RevenueController::class, 'forecast'])->name('forecast');
        Route::get('/transactions', [RevenueController::class, 'transactions'])->name('transactions');
        Route::get('/create', [RevenueController::class, 'create'])->name('create');
        Route::post('/', [RevenueController::class, 'store'])->name('store');
        Route::get('/{revenue}', [RevenueController::class, 'show'])->name('show');
        Route::get('/{revenue}/edit', [RevenueController::class, 'edit'])->name('edit');
        Route::put('/{revenue}', [RevenueController::class, 'update'])->name('update');
        Route::delete('/{revenue}', [RevenueController::class, 'destroy'])->name('destroy');
        Route::post('/{revenue}/confirm', [RevenueController::class, 'confirm'])->name('confirm');
        Route::post('/{revenue}/refund', [RevenueController::class, 'refund'])->name('refund');
        Route::get('/stats', [RevenueController::class, 'getStats'])->name('stats');
        Route::get('/chart-data', [RevenueController::class, 'getChartData'])->name('chart-data');
        Route::get('/export', [RevenueController::class, 'export'])->name('export');
    });

    // Custom Reports Management
    Route::resource('custom-reports', CustomReportController::class)->names('custom-reports');
    Route::prefix('custom-reports')->name('custom-reports.')->group(function () {
        Route::post('/{customReport}/duplicate', [CustomReportController::class, 'duplicate'])->name('duplicate');
        Route::post('/{customReport}/execute', [CustomReportController::class, 'execute'])->name('execute');
        Route::get('/{customReport}/chart-data', [CustomReportController::class, 'chartData'])->name('chart-data');
        Route::get('/{customReport}/export', [CustomReportController::class, 'export'])->name('export');
        Route::get('/columns/{dataSource}', [CustomReportController::class, 'getColumns'])->name('get-columns');
        Route::post('/preview', [CustomReportController::class, 'preview'])->name('preview');
        Route::post('/bulk-action', [CustomReportController::class, 'bulkAction'])->name('bulk-action');
    });

    // Export Management
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/', [ExportController::class, 'index'])->name('index');
        Route::get('/create', [ExportController::class, 'create'])->name('create');
        Route::post('/', [ExportController::class, 'store'])->name('store');
        Route::get('/{export}', [ExportController::class, 'show'])->name('show');
        Route::get('/{export}/edit', [ExportController::class, 'edit'])->name('edit');
        Route::put('/{export}', [ExportController::class, 'update'])->name('update');
        Route::delete('/{export}', [ExportController::class, 'destroy'])->name('destroy');


        // Additional views
        Route::get('/scheduled/index', [ExportController::class, 'scheduled'])->name('scheduled');

        // AJAX endpoints
        Route::post('/bulk-action', [ExportController::class, 'bulk'])->name('bulk');
        Route::get('/columns/{dataType}', [ExportController::class, 'columns'])->name('columns');
        Route::get('/stats/data', [ExportController::class, 'stats'])->name('stats');
    });

    // Compliance Management (GDPR)
    Route::prefix('compliance')->name('compliance.')->group(function () {
        Route::get('/', [ComplianceController::class, 'index'])->name('index');
        Route::get('/consent-logs', [ComplianceController::class, 'consentLogs'])->name('consent-logs');
        Route::get('/data-requests', [ComplianceController::class, 'dataRequests'])->name('data-requests');
        Route::get('/retention-policies', [ComplianceController::class, 'retentionPolicies'])->name('retention-policies');

        // Data request processing
        Route::post('/process-request/{dataRequest}', [ComplianceController::class, 'processDataRequest'])->name('process-request');
        Route::get('/download-export/{dataRequest}', [ComplianceController::class, 'downloadExport'])->name('download-export');

        // Retention policy execution
        Route::post('/execute-retention-policy/{policy}', [ComplianceController::class, 'executeRetentionPolicy'])->name('execute-retention-policy');

        // API endpoints
        Route::get('/audit', [ComplianceController::class, 'audit'])->name('audit');
    });
});
