<?php

use App\Http\Controllers\EmailCampaignController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\SmtpConfigController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Email Routes
|--------------------------------------------------------------------------
*/

// Email Campaigns
Route::resource('email-campaigns', EmailCampaignController::class)->names([
    'index' => 'email.campaigns.index',
    'create' => 'email.campaigns.create',
    'store' => 'email.campaigns.store',
    'show' => 'email.campaigns.show',
    'edit' => 'email.campaigns.edit',
    'update' => 'email.campaigns.update',
    'destroy' => 'email.campaigns.destroy',
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
    'destroy' => 'email.templates.destroy',
])->parameters(['email-templates' => 'email_template']);

Route::prefix('email-templates')->name('email.templates.')->group(function () {
    Route::get('/{email_template}/preview', [EmailTemplateController::class, 'preview'])->name('preview');
    Route::post('/{email_template}/duplicate', [EmailTemplateController::class, 'duplicate'])->name('duplicate');
});

// SMTP Configurations
Route::resource('smtp-configs', SmtpConfigController::class)->names([
    'index' => 'smtp-configs.index',
    'create' => 'smtp-configs.create',
    'store' => 'smtp-configs.store',
    'show' => 'smtp-configs.show',
    'edit' => 'smtp-configs.edit',
    'update' => 'smtp-configs.update',
    'destroy' => 'smtp-configs.destroy',
]);

Route::prefix('smtp-configs')->name('smtp-configs.')->group(function () {
    Route::post('/{smtpConfig}/test', [SmtpConfigController::class, 'test'])->name('test');
    Route::patch('/{smtpConfig}/toggle', [SmtpConfigController::class, 'toggle'])->name('toggle');
    Route::post('/{smtpConfig}/duplicate', [SmtpConfigController::class, 'duplicate'])->name('duplicate');
    Route::post('/{smtpConfig}/reset-counters', [SmtpConfigController::class, 'resetCounters'])->name('reset-counters');
    Route::get('/providers', [SmtpConfigController::class, 'getProviders'])->name('providers');
    Route::get('/provider-settings/{provider}', [SmtpConfigController::class, 'getProviderSettings'])->name('provider-settings');
});
