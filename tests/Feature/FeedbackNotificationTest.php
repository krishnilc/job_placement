<?php

namespace Tests\Feature;

use App\Mail\FeedbackSubmitted;
use App\Models\ApplicationStatus;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class FeedbackNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mail.contact.address' => 'officer@example.com']);
    }

    private function application(): JobApplication
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $student = User::factory()->create(['role' => 'student']);
        $status = ApplicationStatus::create(['name' => 'Placed', 'category' => 'Successful', 'sort_order' => 7]);
        $job = Job::factory()->create([
            'user_id' => $employer->id, 'title' => 'Graduate Developer',
            'company_name' => 'Example Company',
            'job_type_id' => JobType::factory(), 'category_id' => Category::factory(),
        ]);

        return JobApplication::create([
            'job_id' => $job->id, 'user_id' => $student->id,
            'employer_id' => $employer->id, 'application_status_id' => $status->id,
            'applied_at' => now(),
        ]);
    }

    public static function submissionRoles(): array
    {
        return ['student' => ['student'], 'employer' => ['employer']];
    }

    private function routeFor(string $role, JobApplication $application): string
    {
        return route($role === 'student' ? 'account.feedback.store' : 'admin.jobApplications.feedback.store', $application);
    }

    #[DataProvider('submissionRoles')]
    public function test_feedback_emails_only_the_officer_with_correct_details(string $role): void
    {
        Mail::fake();
        $application = $this->application();
        $sender = $role === 'student' ? $application->user : $application->employer;
        $type = $role === 'student' ? Feedback::TYPE_STUDENT_TO_EMPLOYER : Feedback::TYPE_EMPLOYER_TO_STUDENT;
        $this->actingAs($sender)->post($this->routeFor($role, $application), [
            'rating' => 4, 'comments' => "Great placement.\nHelpful team.",
        ])->assertRedirect()->assertSessionHas('success', 'Thank you! Your feedback has been submitted and the Placement Officer has been notified.');

        Mail::assertSent(FeedbackSubmitted::class, function ($mail) use ($sender, $type, $application) {
            return $mail->hasTo('officer@example.com')
                && count($mail->to) === 1
                && $mail->feedback->given_by === $sender->id
                && $mail->feedback->feedback_type === $type
                && $mail->feedback->job_application_id === $application->id
                && $mail->feedback->rating === 4
                && $mail->feedback->comments === "Great placement.\nHelpful team."
                && $mail->envelope()->replyTo[0]->address === $sender->email;
        });
        Mail::assertSentCount(1);
        $this->assertDatabaseCount('feedbacks', 1);

        $this->post($this->routeFor($role, $application), ['rating' => 3])
            ->assertRedirect()->assertSessionHas('error', 'You have already submitted feedback for this application.');
        Mail::assertSentCount(1);
    }

    #[DataProvider('submissionRoles')]
    public function test_delivery_failure_preserves_feedback_and_shows_explicit_warning(string $role): void
    {
        Exceptions::fake();
        $application = $this->application();
        $sender = $role === 'student' ? $application->user : $application->employer;
        $exception = new TransportException('Mail unavailable');
        $pending = Mockery::mock(PendingMail::class);
        Mail::shouldReceive('to')->once()->with('officer@example.com')->andReturn($pending);
        $pending->shouldReceive('send')->once()->with(Mockery::type(FeedbackSubmitted::class))->andThrow($exception);
        $returnRoute = route($role === 'student' ? 'account.myJobApplications' : 'admin.jobApplications');

        $this->actingAs($sender)->from($returnRoute)->post($this->routeFor($role, $application), ['rating' => 5])
            ->assertRedirect($returnRoute)->assertSessionHas('error')->assertSessionMissing('success');
        $this->assertDatabaseCount('feedbacks', 1);
        Exceptions::assertReported(fn (TransportException $reported) => $reported === $exception);
        $this->get($returnRoute)->assertOk()
            ->assertSee('Your feedback has been saved, but the notification email to the Placement Officer could not be sent.')
            ->assertSee('Do not submit it again.');
    }

    public function test_invalid_unauthorized_and_pre_placement_feedback_send_no_email(): void
    {
        Mail::fake();
        $application = $this->application();
        $url = $this->routeFor('student', $application);
        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->post($url, ['rating' => 5])->assertForbidden();
        $this->actingAs($application->user);
        foreach ([[], ['rating' => 0], ['rating' => 6], ['rating' => 5, 'comments' => str_repeat('x', 2001)]] as $data) {
            $this->postJson($url, $data)->assertUnprocessable();
        }
        $status = ApplicationStatus::create(['name' => 'Submitted', 'category' => 'Active', 'sort_order' => 1]);
        $application->update(['application_status_id' => $status->id]);
        $this->post($url, ['rating' => 5])->assertSessionHas('error');
        Mail::assertNothingSent();
        $this->assertDatabaseCount('feedbacks', 0);
    }

    #[DataProvider('submissionRoles')]
    public function test_real_mailer_renders_feedback_and_escapes_comments(string $role): void
    {
        config(['mail.default' => 'array']);
        $application = $this->application();
        $sender = $role === 'student' ? $application->user : $application->employer;
        $this->actingAs($sender)->post($this->routeFor($role, $application), [
            'rating' => 5, 'comments' => "First line\n<script>alert(1)</script>",
        ])->assertRedirect()->assertSessionHas('success');
        $messages = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $email = $messages[0]->getOriginalMessage();
        $this->assertSame('officer@example.com', $email->getTo()[0]->getAddress());
        $this->assertSame($sender->email, $email->getReplyTo()[0]->getAddress());
        $this->assertSame('New '.$role.' feedback: Graduate Developer', $email->getSubject());
        $this->assertStringContainsString('Example Company', $email->getHtmlBody());
        $this->assertStringContainsString($sender->name, $email->getHtmlBody());
        $this->assertStringContainsString('5/5', $email->getHtmlBody());
        $this->assertStringContainsString("First line\n&lt;script&gt;alert(1)&lt;/script&gt;", $email->getHtmlBody());
        $this->assertStringNotContainsString('<script>', $email->getHtmlBody());
        $this->assertStringContainsString('Review feedback', $email->getHtmlBody());
        $this->assertCount(0, $email->getAttachments());
    }
}
