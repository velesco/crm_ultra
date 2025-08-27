<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// WhatsApp API Routes
Route::prefix('whatsapp')->group(function () {
    Route::post('/webhook', [App\Http\Controllers\WhatsAppController::class, 'webhook'])->name('api.whatsapp.webhook');
});

// Dashboard API Routes
Route::prefix('dashboard')->middleware('auth:sanctum')->group(function () {
    Route::get('/stats', [App\Http\Controllers\DashboardController::class, 'getStats']);
    Route::get('/recent-activity', [App\Http\Controllers\DashboardController::class, 'getRecentActivity']);
    Route::get('/system-status', [App\Http\Controllers\DashboardController::class, 'getSystemStatus']);
    Route::get('/chart-data', [App\Http\Controllers\DashboardController::class, 'getChartData']);
    Route::get('/stream', [App\Http\Controllers\DashboardController::class, 'streamUpdates']);
});
