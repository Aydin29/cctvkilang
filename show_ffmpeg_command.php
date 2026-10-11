<?php
/**
 * Print exact FFmpeg command untuk testing
 * Usage: php show_ffmpeg_command.php [cctv_slug]
 */

// Prefer composer autoload for modern projects, fallback for older Laravel
$require = __DIR__ . '/vendor/autoload.php';
if (file_exists($require)) {
    require_once $require;
} else {
    require_once __DIR__ . '/bootstrap/autoload.php';
}
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cctv;
use App\Services\StreamConversionService;

$slug = isset($argv[1]) ? trim($argv[1]) : null;

if (!$slug) {
    echo "=== All CCTVs ===\n";
    $cctvs = Cctv::all();
    foreach ($cctvs as $cctv) {
        echo sprintf("%-30s | Slug: %s\n", substr($cctv->name, 0, 29), $cctv->slug);
    }
    echo "\nUsage: php show_ffmpeg_command.php \"cctv_slug\"\n";
    echo "Example: php show_ffmpeg_command.php \"ip 43\"\n";
    exit;
}

$cctv = Cctv::where('slug', $slug)->first();
if (!$cctv) {
    echo "❌ CCTV not found for slug: {$slug}\n";
    exit(1);
}

echo "=== CCTV Info ===\n";
echo "Name: {$cctv->name}\n";
echo "Slug: {$cctv->slug}\n";
echo "RTSP URL: {$cctv->stream_url}\n";
echo "\n";

// Recreate the exact command from StreamConversionService
$streamFolder = $cctv->slug;
$inputUrl = $cctv->stream_url;
$outputDir = public_path("stream/{$cctv->slug}");

// Build input options
$inputOptions = "-rtsp_transport tcp -stimeout 5000000 -analyzeduration 5000000 -probesize 5000000";

// Build output options
$videoCodec = "h264_qsv";
$preset = "veryfast";
$tune = "zerolatency";
$targetFps = 25;
$gopSize = 25;
$threads = 4;

$filters = "fps={$targetFps}";

$command = "C:\\ffmpeg\\bin\\ffmpeg.exe " .
    "{$inputOptions} " .
    "-i \"{$inputUrl}\" " .
    "-c:v {$videoCodec} " .
    "-preset {$preset} " .
    "-tune {$tune} " .
    "-vf {$filters} " .
    "-r {$targetFps} " .
    "-g {$gopSize} " .
    "-threads {$threads} " .
    "-f hls " .
    "-hls_time 4 " .
    "-hls_list_size 5 " .
    "-hls_flags delete_segments " .
    "\"{$outputDir}\\playlist_%d.ts\" " .
    "\"{$outputDir}\\playlist.m3u8\"";

echo "=== Exact FFmpeg Command ===\n";
echo $command . "\n";
echo "\n";

// Show what each part does
echo "=== Parameter Explanation ===\n";
echo "Input options:\n";
echo "  -rtsp_transport tcp       : Use TCP for RTSP (more reliable than UDP)\n";
echo "  -stimeout 5000000         : Socket timeout 5 seconds\n";
echo "  -analyzeduration 5000000  : Analyze stream for 5 seconds\n";
echo "  -probesize 5000000        : Probe 5MB of stream\n";
echo "\n";
echo "Output options:\n";
echo "  -c:v h264_qsv             : Use Intel QuickSync hardware encoding\n";
echo "  -preset veryfast          : Encoding speed (veryfast = lowest latency)\n";
echo "  -tune zerolatency         : Optimize for live streaming\n";
echo "  -vf fps=25                : Normalize frame rate to 25 fps\n";
echo "  -r 25                     : Output rate 25 fps\n";
echo "  -g 25                     : GOP size 25 (keyframe interval)\n";
echo "  -f hls                    : Output format: HLS\n";
echo "  -hls_time 4               : Segment duration 4 seconds\n";
echo "  -hls_list_size 5          : Keep 5 segments in playlist\n";
echo "  -hls_flags delete_segments: Delete old segments\n";
echo "\n";

// Save command to file for easy copy-paste
$batchFile = __DIR__ . '/test_command_' . str_replace(' ', '_', $slug) . '.bat';
$batchContent = "@echo off\n";
$batchContent .= "REM Auto-generated test for: {$cctv->name}\n";
$batchContent .= "setlocal enabledelayedexpansion\n";
$batchContent .= "\n";
$batchContent .= "if not exist \"{$outputDir}\" mkdir \"{$outputDir}\"\n";
$batchContent .= "\n";
$batchContent .= "echo Starting FFmpeg test...\n";
$batchContent .= "echo Output dir: {$outputDir}\n";
$batchContent .= "echo.\n";
$batchContent .= "\n";
$batchContent .= $command . "\n";
$batchContent .= "\n";
$batchContent .= "if exist \"{$outputDir}\\playlist.m3u8\" (\n";
$batchContent .= "    echo [SUCCESS] Playlist created!\n";
$batchContent .= "    dir /b \"{$outputDir}\"\n";
$batchContent .= ") else (\n";
$batchContent .= "    echo [FAILED] Playlist not created!\n";
$batchContent .= ")\n";
$batchContent .= "\n";
$batchContent .= "pause\n";

file_put_contents($batchFile, $batchContent);
echo "=== Saved ===\n";
echo "Created test batch file: " . basename($batchFile) . "\n";
echo "You can run it directly without parameters\n";
