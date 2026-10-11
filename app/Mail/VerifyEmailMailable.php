<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $verificationUrl;
    public string $userName;
    public ?string $bodyImageCid = null;

    /**
     * Create a new message instance.
     */
    public function __construct(string $verificationUrl, string $userName)
    {
        $this->verificationUrl = $verificationUrl;
        $this->userName = $userName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifikasi Alamat Email - CCTV Pertamina',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'vendor.notifications.email-verification',
            with: [
                'actionUrl' => $this->verificationUrl,
                'notifiable' => (object) ['name' => $this->userName],
                'bodyImageCid' => $this->bodyImageCid,
            ],
        );
    }

    /**
     * Embed the Pertamina logo as inline CID so clients render it and not as attachment.
     */
    public function build()
    {
        // only embed PPN3 body image
        $bodyPath = public_path('images/PPN3.png');

        $this->bodyImageCid = 'ppn3-body-'.sha1($this->verificationUrl).'@symfony';
        $this->viewData['bodyImageCid'] = $this->bodyImageCid;

        if (file_exists($bodyPath)) {
            $this->withSymfonyMessage(function ($message) use ($bodyPath) {
                $part = new \Symfony\Component\Mime\Part\DataPart(
                    new \Symfony\Component\Mime\Part\File($bodyPath)
                );
                $part->asInline();
                $part->setContentId($this->bodyImageCid);
                $message->addPart($part);
            });
        }

        return $this;
    }

}
