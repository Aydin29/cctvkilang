<?php
/**
 * Script untuk menjalankan streaming untuk SEMUA CCTV
 * Ini adalah wrapper yang menggunakan StreamConversionService
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$request = Illuminate\Http\Request::capture();
$kernel->bootstrap();

use App\Models\Cctv;
use App\Services\StreamConversionService;
use Illuminate\Support\Facades\Log;

$streamService = app(StreamConversionService::class);

// Get all CCTV
$cctvs = Cctv::whereNotNull('stream_url')->get();

if ($cctvs->isEmpty()) {
    echo "❌ Tidak ada CCTV dengan stream_url yang dikonfigurasi\n";
    exit(1);
}

echo "═══════════════════════════════════════════════════════════\n";
echo "  MEMULAI STREAMING UNTUK SEMUA CCTV\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "\n";
echo "Total CCTV: {$cctvs->count()}\n";
echo "Waktu: " . date('Y-m-d H:i:s') . "\n";
echo "\n";

$successful = 0;
$failed = 0;
$results = [];

foreach ($cctvs as $cctv) {
    echo "────────────────────────────────────────────────────────\n";
    echo "CCTV: {$cctv->name} (ID: {$cctv->id})\n";
    echo "IP: {$cctv->ip_address}\n";
    echo "Status: {$cctv->status}\n";
    
    if (!$cctv->stream_url) {
        echo "⚠️  SKIP: stream_url tidak dikonfigurasi\n";
        $results[$cctv->id] = [
            'name' => $cctv->name,
            'status' => 'skipped',
            'reason' => 'stream_url not configured'
        ];
        continue;
    }
    
    echo "Stream URL: {$cctv->stream_url}\n";
    echo "\n";
    
    // Check connection first
    echo "→ Testing RTSP connection...\n";
    if ($streamService->testConnection($cctv)) {
        echo "  ✓ RTSP connection OK\n";
    } else {
        echo "  ⚠️  RTSP connection failed (may be network issue)\n";
    }
    
    // Start conversion
    echo "→ Starting FFmpeg conversion...\n";
    
    $startTime = microtime(true);
    $result = $streamService->convertRtspToM3u8($cctv);
    $elapsed = microtime(true) - $startTime;
    
    if ($result['success']) {
        echo "  ✓ SUCCESS ({$elapsed}s)\n";
        echo "  Playlist: {$result['url']}\n";
        echo "  Output: {$result['output']}\n";
        $successful++;
        $results[$cctv->id] = [
            'name' => $cctv->name,
            'status' => 'success',
            'time' => round($elapsed, 2),
            'url' => $result['url']
        ];
    } else {
        echo "  ✗ FAILED ({$elapsed}s)\n";
        echo "  Error: " . substr($result['error'], 0, 100) . "...\n";
        $failed++;
        $results[$cctv->id] = [
            'name' => $cctv->name,
            'status' => 'failed',
            'error' => $result['error']
        ];
    }
    
    echo "\n";
}

// Summary
echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  SUMMARY\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "Successful: {$successful}\n";
echo "Failed: {$failed}\n";
echo "Total: {$cctvs->count()}\n";
echo "\n";

// Detailed results
echo "HASIL DETAIL:\n";
echo "────────────────────────────────────────────────────────\n";
foreach ($results as $result) {
    $status = match ($result['status']) {
        'success' => '✓',
        'failed' => '✗',
        'skipped' => '⊘'
    };
    echo "{$status} {$result['name']}: {$result['status']}\n";
    if (isset($result['time'])) {
        echo "    Time: {$result['time']}s\n";
    }
    if (isset($result['error'])) {
        echo "    Error: " . substr($result['error'], 0, 80) . "...\n";
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";

// Keep process running - let FFmpeg continue in background
echo "\n✓ Semua proses FFmpeg dimulai. Mereka akan berjalan di background.\n";
echo "✓ Tekan Ctrl+C untuk keluar dari script ini (FFmpeg tetap berjalan).\n";
echo "\n";

// Wait indefinitely
while (true) {
    sleep(1);
}
