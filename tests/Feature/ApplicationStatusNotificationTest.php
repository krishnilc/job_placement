<?php

namespace Tests\Feature;

use App\Models\ApplicationStatus;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\User;
use App\Notifications\ApplicationStatusChanged;
use Database\Seeders\ApplicationStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class ApplicationStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationStatusSeeder::class);
        config(['mail.contact.address' => 'officer@example.com']);
    }

    private function application(): JobApplication
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $student = User::factory()->create(['role' => 'student']);
        $job = Job::factory()->create([
            'user_id' => $employer->id, 'title' => 'Graduate Developer',
            'company_name' => 'Example Company',
            'job_type_id' => JobType::factory(),
            'category_id' => Category::factory(),
        ]);

        return JobApplication::create([
            'job_id' => $job->id, 'user_id' => $student->id,
            'employer_id' => $employer->id, 'application_status_id' => 1,
            'status' => 'pending', 'applied_at' => now(),
        ]);
    }

    public function test_admins_and_super_admins_notify_applicants_for_status_changes(): void
    {
        Notification::fake();
        $application = $this->application();
        foreach (['admin', 'super_admin'] as $role) {
            $actor = User::factory()->create(['role' => $role]);
            $this->actingAs($actor);
            foreach (ApplicationStatus::orderBy('sort_order')->get() as $status) {
                $previous = $application->fresh()->applicationStatus->name;
                if ($previous === $status->name) {
                    continue;
                }
                $this->from(route('admin.jobApplications'))
                    ->patch(route('admin.jobApplications.status', $application), ['application_status_id' => $status->id])
                    ->assertRedirect(route('admin.jobApplications'))
                    ->assertSessionHas('success', 'Application status updated successfully. The applicant has been notified by email.');
                $this->assertSame($status->id, $application->fresh()->application_status_id);
                $this->assertDatabaseHas('application_status_history', [
                    'job_application_id' => $application->id,
                    'application_status_id' => $status->id, 'changed_by' => $actor->id,
                ]);
                Notification::assertSentTo($application->user, ApplicationStatusChanged::class,
                    fn ($notification) => $notification->previousStatus === $previous
                        && $notification->currentStatus === $status->name
                        && $notification->jobTitle === 'Graduate Developer'
                        && $notification->companyName === 'Example Company');
            }
        }
        Notification::assertCount(17);
        Notification::assertNotSentTo($application->employer, ApplicationStatusChanged::class);
    }

    public function test_employer_can_notify_only_for_their_own_job(): void
    {
        Notification::fake();
        $application = $this->application();
        $this->actingAs($application->employer)
            ->patch(route('admin.jobApplications.status', $application), ['application_status_id' => 2])
            ->assertRedirect();
        Notification::assertSentToTimes($application->user, ApplicationStatusChanged::class, 1);

        $this->actingAs(User::factory()->create(['role' => 'employer']))
            ->patch(route('admin.jobApplications.status', $application), ['application_status_id' => 3])
            ->assertForbidden();
        $this->assertSame(2, $application->fresh()->application_status_id);
        Notification::assertCount(1);
        $this->assertDatabaseCount('application_status_history', 1);
    }

    public function test_same_status_does_not_create_duplicate_mail_or_history(): void
    {
        Notification::fake();
        $application = $this->application();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch(route('admin.jobApplications.status', $application), ['application_status_id' => 1])
            ->assertSessionHas('success', 'Application status is unchanged; no notification email was sent.');
        Notification::assertNothingSent();
        $this->assertDatabaseCount('application_status_history', 0);
    }

    public function test_invalid_status_and_student_requests_cannot_notify(): void
    {
        Notification::fake();
        $application = $this->application();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach ([[], ['application_status_id' => 999], ['application_status_id' => 'invalid']] as $data) {
            $this->patchJson(route('admin.jobApplications.status', $application), $data)
                ->assertUnprocessable()->assertJsonValidationErrors('application_status_id');
        }
        $this->actingAs($application->user)
            ->patch(route('admin.jobApplications.status', $application), ['application_status_id' => 2])
            ->assertRedirect();
        $this->assertSame(1, $application->fresh()->application_status_id);
        Notification::assertNothingSent();
        $this->assertDatabaseCount('application_status_history', 0);
    }

    public function test_mail_failure_preserves_status_and_history_and_shows_warning(): void
    {
        Exceptions::fake();
        $application = $this->application();
        $exception = new TransportException('Mail unavailable');
        Notification::shouldReceive('send')->once()->andThrow($exception);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->from(route('admin.jobApplications'))
            ->patch(route('admin.jobApplications.status', $application), ['application_status_id' => 7])
            ->assertRedirect(route('admin.jobApplications'))
            ->assertSessionHas('error')->assertSessionMissing('success');
        $this->assertSame(7, $application->fresh()->application_status_id);
        $this->assertDatabaseHas('application_status_history', [
            'job_application_id' => $application->id, 'application_status_id' => 7, 'changed_by' => $admin->id,
        ]);
        Exceptions::assertReported(fn (TransportException $reported) => $reported === $exception);
        $this->get(route('admin.jobApplications'))->assertOk()
            ->assertSee('Application status and history were saved, but the notification email could not be sent.');
    }

    public function test_legacy_application_without_status_relation_can_notify(): void
    {
        Notification::fake();
        $application = $this->application();
        $application->update(['application_status_id' => null]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch(route('admin.jobApplications.status', $application), ['application_status_id' => 2])
            ->assertRedirect()->assertSessionHas('success');
        Notification::assertSentTo($application->user, ApplicationStatusChanged::class,
            fn ($notification) => $notification->previousStatus === 'Pending'
                && $notification->currentStatus === 'Under Review');
    }

    public function test_every_status_has_appropriate_next_steps(): void
    {
        $student = User::factory()->create();
        $expectedMessages = [
            'Submitted' => 'Your application is marked as submitted and is awaiting review.',
            'Under Review' => 'Your application is being reviewed. You will be notified of further status changes.',
            'Shortlisted' => 'You have been shortlisted. Please watch for further communication about the next steps.',
            'Interview Scheduled' => 'Your application is marked as Interview Scheduled. Please contact the employer or Placement Officer to confirm the interview date, time, and arrangements.',
            'Interview Completed' => 'Your interview is marked as completed. Please await further communication about the outcome.',
            'Accepted' => 'Your application has been accepted. Please contact the employer or Placement Officer to confirm the next steps.',
            'Placed' => 'Congratulations! Your application is marked as placed. Please confirm your placement arrangements with the employer or Placement Officer.',
            'Rejected' => 'Unfortunately, your application has not been successful. You can continue exploring other placement opportunities.',
            'Withdrawn' => 'Your application is marked as withdrawn. If this was unexpected, please contact the Placement Officer.',
        ];
        $this->assertSame(ApplicationStatus::orderBy('sort_order')->pluck('name')->all(), array_keys($expectedMessages));
        foreach ($expectedMessages as $status => $message) {
            $mail = (new ApplicationStatusChanged('Developer', 'Example Company', 'Submitted', $status))->toMail($student);
            $this->assertContains($message, $mail->introLines);
            $this->assertSame('FNU Job Placement application status: '.$status, $mail->subject);
            $this->assertSame(route('account.myJobApplications'), $mail->actionUrl);
            $this->assertSame([['officer@example.com', 'Placement Officer']], $mail->replyTo);
        }
    }

    public function test_real_mailer_renders_application_details_and_addresses_applicant(): void
    {
        config(['mail.default' => 'array']);
        $application = $this->application();
        $application->job->title = '<b>Developer</b>';
        $application->job->save();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch(route('admin.jobApplications.status', $application), ['application_status_id' => 3])
            ->assertRedirect()->assertSessionHas('success');

        $messages = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $email = $messages[0]->getOriginalMessage();
        $this->assertSame($application->user->email, $email->getTo()[0]->getAddress());
        $this->assertSame('officer@example.com', $email->getReplyTo()[0]->getAddress());
        $this->assertStringContainsString('&lt;b&gt;Developer&lt;/b&gt;', $email->getHtmlBody());
        $this->assertStringContainsString('Example Company', $email->getHtmlBody());
        $this->assertStringContainsString('You have been shortlisted.', $email->getHtmlBody());
    }
}
