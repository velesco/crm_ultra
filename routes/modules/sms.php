<?php

use App\Http\Controllers\SmsController;
use App\Http\Controllers\SmsProviderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SMS Routes
|--------------------------------------------------------------------------
*/

// SMS Management
Route::resource('sms', SmsController::class);
Route::prefix('sms')->name('sms.')->group(function () {
    Route::post('/send-to-segment', [SmsController::class, 'sendToSegment'])->name('send-to-segment');
    Route::post('/bulk', [SmsController::class, 'sendBulk'])->name('bulk');
    Route::get('/statistics', [SmsController::class, 'statistics'])->name('statistics');
    Route::post('/{smsMessage}/cancel', [SmsController::class, 'cancel'])->name('cancel');
    Route::post('/{smsMessage}/resend', [SmsController::class, 'resend'])->name('resend');
    Route::post('/{smsMessage}/retry', [SmsController::class, 'retry'])->name('retry');
});

// SMS Providers
Route::resource('sms-providers', SmsProviderController::class)->names([
    'index' => 'sms.providers.index',
    'create' => 'sms.providers.create',
    'store' => 'sms.providers.store',
    'show' => 'sms.providers.show',
    'edit' => 'sms.providers.edit',
    'update' => 'sms.providers.update',
    'destroy' => 'sms.providers.destroy',
]);

Route::prefix('sms-providers')->name('sms.providers.')->group(function () {
    Route::post('/{provider}/test', [SmsProviderController::class, 'test'])->name('test');
    Route::post('/{provider}/toggle-active', [SmsProviderController::class, 'toggleActive'])->name('toggle-active');
});
