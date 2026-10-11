<?php
require __DIR__ . "/../vendor/autoload.php";

// bootstrap the framework so helpers like public_path() are available
$app = require __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Mail\VerifyEmailMailable;

$m = new VerifyEmailMailable('https://example.com/verify', 'Test User');
$m->build();
$html = $m->render();

echo $html;
