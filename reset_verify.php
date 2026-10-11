<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

// Get first user
$user = User::first();

if (!$user) {
    echo "No user found!\n";
    exit;
}

// Mark as verified again
$user->email_verified_at = now();
$user->save();

echo "User email verified_at restored to: " . $user->email_verified_at . "\n";
echo "User is now verified: " . ($user->hasVerifiedEmail() ? 'Yes' : 'No') . "\n";

