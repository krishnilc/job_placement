<?php

namespace App\Mail;

use App\Models\Job;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class EmployerJobPosted extends Mailable
{
    public function __construct(public Job $job) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->job->user->email, $this->job->user->name)],
            subject: 'Employer job awaiting approval: '.$this->job->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'email.employer-job-posted');
    }
}
