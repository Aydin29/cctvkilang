<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Password;
use App\Models\User;

$user = User::first();
echo "sending to: " . $user->email . "\n";
$status = Password::sendResetLink(['email' => $user->email]);
echo "status: $status\n";
