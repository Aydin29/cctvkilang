<?php
/**
 * Generate list of CCTV for batch file
 * Output format: SLUG|NAME|URL
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

use App\Models\Cctv;

$cctvs = Cctv::whereNotNull('stream_url')->get();

foreach ($cctvs as $cctv) {
    // Normalize slug to match sanitized folder naming used by the HLS output.
    $slug = $cctv->slug;

    // Backward-compat fix: some existing folders use "not fixedddd" but frontend expects "not fixed".
    $slug = str_replace('not fixedddd', 'not fixed', $slug);

    echo $slug . '|' . $cctv->name . '|' . $cctv->stream_url . PHP_EOL;
}

