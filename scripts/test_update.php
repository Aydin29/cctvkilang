<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Http\Request;
$request = Request::create('/maps/update-location/1','POST',['latitude'=>-6.0,'longitude'=>108.0]);
$response = $app->handle($request);
echo $response->getContent(),"\n";
