<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactConfirmation extends Mailable
{
    public function __construct(
        public string $senderName,
        public string $contactSubject,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('mail.contact.address'), 'Placement Officer')],
            subject: 'We have received your message: '.$this->contactSubject,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'email.contact-confirmation');
    }
}
