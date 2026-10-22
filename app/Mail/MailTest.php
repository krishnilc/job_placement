<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MailTest extends Mailable
{
    public function __construct(public string $requestedAt) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'FNU Job Placement - Test Email');
    }

    public function content(): Content
    {
        return new Content(view: 'email.mail-test');
    }
}
