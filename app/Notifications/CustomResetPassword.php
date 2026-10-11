<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends Notification
{
    use Queueable;

    /**
     * Token reset password
     */
    protected string $token;

    /**
     * Buat instance baru notifikasi reset password.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Saluran pengiriman notifikasi.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Format email notifikasi.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        /*
         * build a predictable content-id and pass it to the view so the
         * <img> tag can reference the same value.  The mail-level callback
         * will then manually create a DataPart, mark it inline and attach it
         * with that exact CID.  Symfony's helper embedFromPath does *not*
         * allow controlling the Content-ID; its second argument becomes the
         * filename, which is why images were appearing broken previously.
         */
        $bodyCid = 'ppn3-body-'.sha1($notifiable->email).'@symfony';

        $mail = (new MailMessage)
            ->subject('Reset Password - CCTV Kilang Pertamina RU VI')
            ->view('emails.password-reset-custom', [
                'actionUrl' => $resetUrl,
                'user' => $notifiable,
                'bodyImageCid' => $bodyCid,
            ]);

        $bodyPath = public_path('images/PPN3.png');
        if (file_exists($bodyPath)) {
            // embed the logo inline with a predictable content-id so it can be
            // referenced from the HTML view above the "Reset Kata Sandi Anda" heading.
            $mail->withSymfonyMessage(function ($message) use ($bodyPath, $bodyCid) {
                // construct DataPart ourselves so we can set the content-id
                $part = new \Symfony\Component\Mime\Part\DataPart(
                    new \Symfony\Component\Mime\Part\File($bodyPath)
                );
                $part->asInline();
                $part->setContentId($bodyCid);
                $message->addPart($part);
            });
        }

        return $mail;
    }

    /**
     * Data notifikasi dalam bentuk array (opsional).
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
