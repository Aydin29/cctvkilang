<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CctvController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/status', [DashboardController::class, 'getStatus']);
});

// Public route for dashboard stats (for real-time updates)
Route::get('/dashboard/stats', [DashboardController::class, 'getStatus']);

// Ping all CCTV and update status (online/offline)
Route::post('/dashboard/ping-all', [\App\Http\Controllers\Api\CctvStatusBatchController::class, 'pingAll']);

// Public route for RTSP test (used by create/edit form via fetch)
Route::post('/test-rtsp-connection', [CctvController::class, 'testRtspConnection']);

// Public route for IP ping test (used by create/edit form)
Route::post('/test-ip-connection', [\App\Http\Controllers\Api\CctvIpController::class, 'testIpConnection']);




