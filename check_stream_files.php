<?php
/**
 * Diagnostic script to check if stream segment files exist
 * Usage: php check_stream_files.php [cctv_slug]
 */

$require = __DIR__ . '/vendor/autoload.php';
if (file_exists($require)) {
    require_once $require;
} else {
    // Fallback for older Laravel apps
    require_once __DIR__ . '/bootstrap/autoload.php';
}
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cctv;
use Illuminate\Support\Facades\File;

$slug = isset($argv[1]) ? trim($argv[1]) : null;

if (!$slug) {
    // Show all CCTVs and their stream status
    $cctvs = Cctv::all();
    echo "=== All CCTV Streams ===\n";
    foreach ($cctvs as $cctv) {
        $streamDir = public_path("stream/{$cctv->slug}");
        $files = [];
        if (File::exists($streamDir)) {
            $files = File::files($streamDir);
        }
        $fileCount = count($files);
        echo sprintf(
            "%-30s | Slug: %-20s | Dir exists: %-5s | Files: %d\n",
            substr($cctv->name, 0, 29),
            $cctv->slug,
            File::exists($streamDir) ? 'YES' : 'NO',
            $fileCount
        );
        if ($fileCount > 0 && $fileCount <= 10) {
            foreach ($files as $file) {
                echo sprintf("    - %s (%s)\n", basename($file->getPathname()), File::size($file->getPathname()) . ' bytes');
            }
        } elseif ($fileCount > 10) {
            $sampleFiles = array_slice($files, 0, 5);
            foreach ($sampleFiles as $file) {
                echo sprintf("    - %s\n", basename($file->getPathname()));
            }
            echo "    ... and " . ($fileCount - 5) . " more files\n";
        }
    }
} else {
    // Show detailed info for specific slug
    $cctv = Cctv::where('slug', $slug)->first();
    if (!$cctv) {
        echo "❌ CCTV not found for slug: {$slug}\n";
        echo "Available slugs:\n";
        Cctv::all()->each(function ($c) {
            echo "  - {$c->slug}\n";
        });
        exit(1);
    }

    $streamDir = public_path("stream/{$cctv->slug}");
    echo "=== Stream Status for: {$cctv->name} ===\n";
    echo "Slug: {$cctv->slug}\n";
    echo "Stream Dir: {$streamDir}\n";
    echo "Dir Exists: " . (File::exists($streamDir) ? 'YES' : 'NO') . "\n";

    if (!File::exists($streamDir)) {
        echo "❌ Stream directory does not exist!\n";
        exit(1);
    }

    $files = File::files($streamDir);
    echo "Total Files: " . count($files) . "\n";
    echo "\nFiles:\n";
    
    usort($files, function ($a, $b) {
        return strcmp($a->getFilename(), $b->getFilename());
    });

    foreach ($files as $file) {
        $basename = $file->getFilename();
        $size = File::size($file->getPathname());
        $time = date('Y-m-d H:i:s', File::lastModified($file->getPathname()));
        echo sprintf("  %-30s | %10s bytes | Modified: %s\n", $basename, $size, $time);
    }

    // Check M3U8 content
    $m3u8Path = $streamDir . '/playlist.m3u8';
    if (File::exists($m3u8Path)) {
        echo "\n=== Playlist Content ===\n";
        echo File::get($m3u8Path);
    } else {
        echo "\n❌ playlist.m3u8 not found!\n";
    }
}
