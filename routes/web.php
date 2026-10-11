<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\CctvController;
use App\Http\Controllers\CctvAnalysisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StreamController;

// ===================================================================
// Route untuk mengakses file streaming dari direktori storage
// ===================================================================
Route::get('/storage/streams/{folder}/{filename}', function ($folder, $filename) {
    $path = storage_path("app/public/streams/{$folder}/{$filename}");

    if (!File::exists($path)) {
        abort(404, 'File not found.');
    }

    $mimeType = File::mimeType($path);

    return Response::file($path, [
        'Content-Type' => $mimeType,
    ]);
})->where([
    'folder' => '[a-zA-Z0-9_\\-]+',
    'filename' => '[a-zA-Z0-9_\\-\.]+',
]);

// ===================================================================
// Stream routes
// ===================================================================
Route::get('stream/{slug}/playlist.m3u8', [StreamController::class, 'servePlaylist'])->name('stream.get');
Route::get('stream/{slug}/{filename}', [StreamController::class, 'serveSegment'])->name('stream.segment');
Route::post('stream/{slug}/start', [StreamController::class, 'start'])->name('stream.start');

// ===================================================================
// API routes untuk kontrol streaming CCTV
// ===================================================================
Route::prefix('api/cctv')->group(function () {
    Route::post('{id}/start-stream', [CctvController::class, 'startStreaming'])->name('api.cctv.start-stream');
    Route::post('{id}/stop-stream', [CctvController::class, 'stopStreaming'])->name('api.cctv.stop-stream');
});

// ===================================================================
// Redirect root ke halaman login
// ===================================================================
Route::get('/', function () {
    return redirect()->route('login');
});


// ===================================================================
// Route group untuk user terautentikasi
// ===================================================================
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CCTV management routes
    Route::delete('cctvs/bulk-destroy', [CctvController::class, 'bulkDestroy'])->name('cctvs.bulkDestroy');
    Route::resource('cctvs', CctvController::class);

    Route::prefix('cctvs')->name('cctvs.')->group(function () {
        Route::get('/{cctv}/delete', [CctvController::class, 'delete'])->name('delete');
        Route::post('/{cctv}/toggle-status', [CctvController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{cctv}/start-streaming', [CctvController::class, 'startStreaming'])->name('start-streaming');
        Route::post('/{cctv}/stop-streaming', [CctvController::class, 'stopStreaming'])->name('stop-streaming');
        Route::get('/{cctv}/test-connection', [CctvController::class, 'testRtspConnection'])->name('test-connection');
    });

    // CCTV Analysis routes
    Route::prefix('cctvanalys')->name('cctvanalys.')->group(function () {
        Route::get('/', [CctvAnalysisController::class, 'index'])->name('index');
        Route::get('/{cctv}', [CctvAnalysisController::class, 'analyze'])->name('analyze');
        Route::get('/{cctv}/data', [CctvAnalysisController::class, 'getAnalysisData'])->name('data');
        Route::get('/{cctv}/metadata', [CctvAnalysisController::class, 'getMetadata'])->name('metadata');
        // Save a periodic snapshot report
        Route::post('/{cctv}/report', [CctvAnalysisController::class, 'storeReport'])->name('report.store');
    });

    // Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Maps Routes
    Route::prefix('maps')->name('maps.')->group(function () {
        Route::get('/', [MapController::class, 'index'])->name('index');
        Route::get('/cctv/{cctv}', [MapController::class, 'showCctv'])->name('cctv');
        Route::post('/update-location/{cctvId}', [MapController::class, 'updateLocation'])->name('update-location');
        Route::get('/api/cctvs', [MapController::class, 'getCctvData'])->name('api.cctvs');
        Route::get('/live-stream/{cctv}', [MapController::class, 'liveStream'])->name('live-stream');
    });

    // Role Management Routes (Admin only)
    Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
        // Role management removed
    });

    Route::get('/api/cctv-data', [MapController::class, 'getCctvData'])->name('api.cctv.data');
});

// Logout Route
Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');

// Auth
require __DIR__."/auth.php";
