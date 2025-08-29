<?php

use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WhatsAppSessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WhatsApp Routes
|--------------------------------------------------------------------------
*/

// WhatsApp Management
Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
    Route::get('/', [WhatsAppController::class, 'index'])->name('index');
    Route::get('/chat', [WhatsAppController::class, 'chat'])->name('chat');
    Route::get('/chat/{contact}', [WhatsAppController::class, 'chatWithContact'])->name('chat.contact');
    Route::post('/send-message', [WhatsAppController::class, 'sendMessage'])->name('send-message');
    Route::post('/send', [WhatsAppController::class, 'sendMessage'])->name('send');
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
    'destroy' => 'whatsapp.sessions.destroy',
]);

Route::prefix('whatsapp-sessions')->name('whatsapp.sessions.')->group(function () {
    Route::post('/{session}/start', [WhatsAppSessionController::class, 'start'])->name('start');
    Route::post('/{session}/stop', [WhatsAppSessionController::class, 'stop'])->name('stop');
    Route::get('/{session}/qr', [WhatsAppSessionController::class, 'getQR'])->name('qr');
    Route::get('/{session}/status', [WhatsAppSessionController::class, 'getStatus'])->name('status');
});
