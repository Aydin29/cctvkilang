<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$request = Illuminate\Http\Request::capture();
$kernel->bootstrap();

// Start streaming for CCTV TEST
$streamService = app('App\Services\StreamConversionService');
$cctv = App\Models\Cctv::find(2); // ID 2 adalah TEST

if (!$cctv) {
    echo "CCTV not found\n";
    exit(1);
}

echo "Starting stream for: " . $cctv->name . "\n";
echo "RTSP URL: " . $cctv->stream_url . "\n";
echo "Output dir: " . public_path("stream/{$cctv->slug}") . "\n";
echo "\n";

// Test connection first
echo "Testing RTSP connection...\n";
$canConnect = $streamService->testConnection($cctv);
echo "Connection: " . ($canConnect ? "OK\n" : "FAILED\n");
echo "\n";

// Start conversion
echo "Starting FFmpeg conversion...\n";
$startTime = microtime(true);
$result = $streamService->convertRtspToM3u8($cctv);
$elapsed = microtime(true) - $startTime;

echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Time: {$elapsed}s\n";
echo "\n";

if ($result['success']) {
    echo "✓ Playlist URL: " . $result['url'] . "\n";
    $playlistPath = public_path("stream/{$cctv->slug}/playlist.m3u8");
    if (file_exists($playlistPath)) {
        echo "✓ Playlist file exists\n";
        echo "✓ Playlist size: " . filesize($playlistPath) . " bytes\n";
    }
} else {
    echo "✗ Error: " . $result['error'] . "\n";
}

// List files in output directory
$outputDir = public_path("stream/{$cctv->slug}");
echo "\nFiles in {$outputDir}:\n";
$files = scandir($outputDir);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $filePath = $outputDir . DIRECTORY_SEPARATOR . $file;
        $size = filesize($filePath);
        echo "  - $file ($size bytes)\n";
    }
}
