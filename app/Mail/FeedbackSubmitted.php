<?php

namespace App\Mail;

use App\Models\Feedback;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class FeedbackSubmitted extends Mailable
{
    public function __construct(public Feedback $feedback) {}

    public function envelope(): Envelope
    {
        $sender = $this->feedback->givenBy;
        $type = $this->feedback->feedback_type === Feedback::TYPE_EMPLOYER_TO_STUDENT
            ? 'employer' : 'student';

        return new Envelope(
            replyTo: [new Address($sender->email, $sender->name)],
            subject: 'New '.$type.' feedback: '.$this->feedback->jobApplication->job->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'email.feedback-submitted');
    }
}
