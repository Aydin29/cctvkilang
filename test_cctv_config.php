<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$request = Illuminate\Http\Request::capture();
$kernel->bootstrap();

$cctv = App\Models\Cctv::where('slug', 'TEST')->first();
if ($cctv) {
    echo "ID: " . $cctv->id . "\n";
    echo "Name: " . $cctv->name . "\n";
    echo "IP: " . $cctv->ip_address . "\n";
    echo "Username: " . ($cctv->rtsp_username ?: 'null') . "\n";
    echo "Password: " . ($cctv->rtsp_password ?: 'null') . "\n";
    echo "Port: " . $cctv->rtsp_port . "\n";
    echo "Path: " . ($cctv->rtsp_path ?: 'null') . "\n";
    echo "Stream URL: " . ($cctv->stream_url ?: 'null') . "\n";
    echo "Is Streaming: " . ($cctv->is_streaming ? 'Yes' : 'No') . "\n";
} else {
    echo "CCTV TEST tidak ditemukan\n";
}
