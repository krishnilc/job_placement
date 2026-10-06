<?php

namespace App\Mail;

use App\Models\Job;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ApplicationSubmissionConfirmation extends Mailable
{
    public function __construct(
        public User $applicant,
        public Job $job,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('mail.contact.address'), 'Placement Officer')],
            subject: 'Application received: '.$this->job->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'email.application-submission-confirmation');
    }
}
