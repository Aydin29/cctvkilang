<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$request = Illuminate\Http\Request::capture();
$kernel->bootstrap();

use App\Models\Cctv;

$cctvs = Cctv::whereNotNull('stream_url')->get();

echo "Total CCTV dengan stream_url:\n";
echo "═════════════════════════════════════════\n\n";

foreach ($cctvs as $cctv) {
    echo "ID: {$cctv->id}\n";
    echo "Name: {$cctv->name}\n";
    echo "IP: {$cctv->ip_address}\n";
    echo "Status: {$cctv->status}\n";
    echo "Stream URL: {$cctv->stream_url}\n";
    echo "Is Streaming: " . ($cctv->is_streaming ? 'Yes' : 'No') . "\n";
    echo "───────────────────────────────────────\n\n";
}

echo "Total: {$cctvs->count()} CCTV\n";
