<?php

namespace Tests\Feature;

use App\Mail\ContactConfirmation;
use App\Mail\ContactSubmission;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['mail.contact.address' => 'officer@example.com']);
    }

    private function validSubmission(): array
    {
        return [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'subject' => 'Placement enquiry',
            'message' => "Hello,\nI need help finding a placement.",
        ];
    }

    public function test_guest_submission_emails_the_officer_and_confirms_to_the_sender(): void
    {
        Mail::fake();
        $data = $this->validSubmission();

        $response = $this->post(route('front.contact.send'), $data);

        $response->assertRedirect(route('front.contact'))
            ->assertSessionHas('success')
            ->assertSessionMissing('error')
            ->assertSessionMissing('_old_input');

        Mail::assertSent(ContactSubmission::class, function (ContactSubmission $mail) use ($data) {
            return $mail->hasTo('officer@example.com')
                && $mail->senderName === $data['name']
                && $mail->senderEmail === $data['email']
                && $mail->contactSubject === $data['subject']
                && $mail->contactMessage === $data['message']
                && $mail->envelope()->replyTo[0]->address === $data['email'];
        });
        Mail::assertSent(ContactConfirmation::class, function (ContactConfirmation $mail) use ($data) {
            return $mail->hasTo($data['email'])
                && $mail->senderName === $data['name']
                && $mail->contactSubject === $data['subject']
                && $mail->envelope()->replyTo[0]->address === 'officer@example.com';
        });
        Mail::assertSentCount(2);

        $this->get(route('front.contact'))
            ->assertOk()
            ->assertSee('A confirmation email has been sent to your email address.')
            ->assertSee('officer@example.com');
    }

    public function test_invalid_submission_sends_no_emails_and_preserves_input(): void
    {
        Mail::fake();
        $data = [
            'name' => '',
            'email' => 'not-an-email',
            'subject' => str_repeat('s', 256),
            'message' => str_repeat('m', 2001),
        ];

        $this->from(route('front.contact'))->post(route('front.contact.send'), $data)
            ->assertRedirect(route('front.contact'))
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message'])
            ->assertSessionHasInput('email', 'not-an-email');

        Mail::assertNothingSent();
        $this->get(route('front.contact'))->assertOk()->assertSee('is-invalid');
    }

    public function test_officer_delivery_failure_is_reported_and_displays_an_error(): void
    {
        Exceptions::fake();
        $exception = new TransportException('Mail server unavailable');
        $pending = Mockery::mock(PendingMail::class);
        Mail::shouldReceive('to')->once()->with('officer@example.com')->andReturn($pending);
        $pending->shouldReceive('send')->once()->with(Mockery::type(ContactSubmission::class))
            ->andThrow($exception);

        $this->post(route('front.contact.send'), $this->validSubmission())
            ->assertRedirect(route('front.contact'))
            ->assertSessionHas('error')
            ->assertSessionMissing('success')
            ->assertSessionHasInput('message', $this->validSubmission()['message']);

        Exceptions::assertReported(fn (TransportException $reported) => $reported === $exception);
        $this->get(route('front.contact'))->assertOk()
            ->assertSee('Unable to send your message right now.');
    }

    public function test_confirmation_failure_does_not_prompt_resubmission(): void
    {
        Exceptions::fake();
        $exception = new TransportException('Confirmation delivery failed');
        $officerMail = Mockery::mock(PendingMail::class);
        $confirmationMail = Mockery::mock(PendingMail::class);
        Mail::shouldReceive('to')->once()->with('officer@example.com')->andReturn($officerMail);
        $officerMail->shouldReceive('send')->once()->with(Mockery::type(ContactSubmission::class));
        Mail::shouldReceive('to')->once()->with('student@example.com')->andReturn($confirmationMail);
        $confirmationMail->shouldReceive('send')->once()->with(Mockery::type(ContactConfirmation::class))
            ->andThrow($exception);

        $this->post(route('front.contact.send'), $this->validSubmission())
            ->assertRedirect(route('front.contact'))
            ->assertSessionHas('warning')
            ->assertSessionMissing('success')
            ->assertSessionMissing('error')
            ->assertSessionMissing('_old_input');

        Exceptions::assertReported(fn (TransportException $reported) => $reported === $exception);
        $this->get(route('front.contact'))->assertOk()
            ->assertSee('Your message has been sent to the Placement Officer')
            ->assertSee('You do not need to submit the form again.');
    }

    public function test_email_templates_escape_user_content(): void
    {
        $mail = new ContactSubmission(
            '<script>alert(1)</script>',
            'student@example.com',
            '<b>Subject</b>',
            "First line\n<script>alert(2)</script>",
        );

        $mail->assertSeeInHtml('<script>alert(1)</script>')
            ->assertSeeInHtml('<b>Subject</b>')
            ->assertSeeInHtml("First line\n<script>alert(2)</script>")
            ->assertDontSeeInHtml('<script>', false);
        $mail->assertHasSubject('Contact form submission: <b>Subject</b>');
        $mail->assertHasReplyTo('student@example.com');

        $confirmation = new ContactConfirmation('<b>Student</b>', '<b>Subject</b>');
        $confirmation->assertSeeInHtml('<b>Student</b>')
            ->assertSeeInHtml('<b>Subject</b>')
            ->assertSeeInHtml('The Placement Officer will get back to you as soon as possible.');
        $confirmation->assertHasReplyTo('officer@example.com');
    }

    public function test_submission_uses_the_real_mailer_api(): void
    {
        config(['mail.default' => 'array']);

        $this->post(route('front.contact.send'), $this->validSubmission())
            ->assertRedirect(route('front.contact'))
            ->assertSessionHas('success');

        $messages = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(2, $messages);

        $officerMessage = $messages[0]->getOriginalMessage();
        $this->assertSame('officer@example.com', $officerMessage->getTo()[0]->getAddress());
        $this->assertSame('student@example.com', $officerMessage->getReplyTo()[0]->getAddress());
        $this->assertStringContainsString('I need help finding a placement.', $officerMessage->getHtmlBody());

        $confirmationMessage = $messages[1]->getOriginalMessage();
        $this->assertSame('student@example.com', $confirmationMessage->getTo()[0]->getAddress());
        $this->assertSame('officer@example.com', $confirmationMessage->getReplyTo()[0]->getAddress());
        $this->assertStringContainsString('Thank you for contacting', $confirmationMessage->getHtmlBody());
    }
}
