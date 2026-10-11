<?php
/**
 * Helper script untuk mendapatkan RTSP URL dari CCTV
 * Digunakan oleh batch file untuk query database
 * 
 * Usage: php get_cctv_url.php TEST
 */

if (php_sapi_name() !== 'cli') {
    die('This script must be run from command line');
}

$slug = isset($argv[1]) ? trim($argv[1]) : null;

if (!$slug) {
    exit(1);
}

// Setup Laravel
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

use App\Models\Cctv;

$cctv = Cctv::where('slug', $slug)->first();

if (!$cctv || !$cctv->stream_url) {
    exit(1);
}

// Output RTSP URL (without surrounding quotes)
echo $cctv->stream_url;
