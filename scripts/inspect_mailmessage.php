<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Notifications\CustomResetPassword;

$user = User::first();
$notif = new CustomResetPassword('dummy-token');
$mail = $notif->toMail($user);

// send through the mailer and intercept the Symfony message string
$mailer = app('mailer');

// keep mail from actually going out
config(['mail.default' => 'log']);

$mailer->send($mail, [], function ($message) {
    // $message is a Symfony\Component\Mime\Email
    file_put_contents('php://stdout', "----- RAW MESSAGE -----\n");
    file_put_contents('php://stdout', $message->toString());
});
