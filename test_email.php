<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Mail;

// Get first user
$user = User::first();

if (!$user) {
    echo "No user found!\n";
    exit;
}

echo "User: " . $user->email . "\n";
echo "Verified: " . ($user->hasVerifiedEmail() ? 'Yes' : 'No') . "\n";

// Mark as unverified for testing
if ($user->hasVerifiedEmail()) {
    echo "\nMarking user as unverified for testing...\n";
    $user->email_verified_at = null;
    $user->save();
    echo "User marked as unverified.\n";
}

// Send verification email
echo "\nSending verification email...\n";

try {
    $user->sendEmailVerificationNotification();
    echo "Verification email sent successfully!\n";
    echo "Check storage/logs/laravel.log for the email content\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

