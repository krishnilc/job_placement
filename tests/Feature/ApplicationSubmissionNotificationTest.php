<?php

namespace Tests\Feature;

use App\Mail\ApplicationSubmissionConfirmation;
use App\Mail\JobNotificationEmail;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\User;
use Database\Seeders\ApplicationStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class ApplicationSubmissionNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationStatusSeeder::class);
        Storage::fake('applications');
        config(['mail.contact.address' => 'officer@example.com']);
    }

    private function job(string $posterRole = 'employer'): Job
    {
        $poster = User::factory()->create(['role' => $posterRole]);

        return Job::factory()->create([
            'user_id' => $poster->id, 'status' => 1,
            'title' => 'Graduate Developer', 'company_name' => 'Example Company',
            'job_type_id' => JobType::factory(), 'category_id' => Category::factory(),
        ]);
    }

    private function submit(Job $job)
    {
        return $this->postJson(route('applyJob'), [
            'job_id' => $job->id,
            'resume' => UploadedFile::fake()->create('My Resume.pdf', 10, 'application/pdf'),
        ]);
    }

    public function test_submission_sends_to_the_actual_job_poster_and_confirms_to_student(): void
    {
        Mail::fake();
        $student = User::factory()->create(['role' => 'student', 'designation' => 'Alumni']);
        $this->actingAs($student);
        foreach (['employer', 'admin', 'super_admin'] as $role) {
            $job = $this->job($role);
            $this->submit($job)->assertOk()->assertJson([
                'status' => true, 'warning' => false,
                'notifications' => ['job_poster' => true, 'student' => true],
            ]);
            Mail::assertSent(JobNotificationEmail::class, fn ($mail) => $mail->hasTo($job->user->email)
                && $mail->applicant->id === $student->id
                && $mail->job->id === $job->id
                && $mail->envelope()->replyTo[0]->address === $student->email);
            Mail::assertSent(ApplicationSubmissionConfirmation::class, fn ($mail) => $mail->hasTo($student->email)
                && $mail->job->id === $job->id
                && $mail->envelope()->replyTo[0]->address === 'officer@example.com');

            $application = JobApplication::where('job_id', $job->id)->firstOrFail();
            $this->assertSame($job->user_id, $application->employer_id);
            $this->assertSame('Submitted', $application->applicationStatus->name);
            $this->assertDatabaseHas('application_status_history', [
                'job_application_id' => $application->id,
                'application_status_id' => $application->application_status_id,
                'changed_by' => $student->id,
            ]);
            Storage::disk('applications')->assertExists($application->resume_file);
            $this->assertSame('My Resume.pdf', $application->resume_file_name);
        }
        Mail::assertSentCount(6);
    }

    public function test_duplicate_application_does_not_resend_emails(): void
    {
        Mail::fake();
        $job = $this->job();
        $this->actingAs(User::factory()->create(['role' => 'student']));
        $this->submit($job)->assertJsonPath('status', true);
        $this->submit($job)->assertJsonPath('status', false)
            ->assertJsonPath('message', 'You have already applied for this job');
        Mail::assertSentCount(2);
        $this->assertDatabaseCount('job_applications', 1);
        $this->assertDatabaseCount('application_status_history', 1);
    }

    public function test_invalid_application_sends_no_email(): void
    {
        Mail::fake();
        $job = $this->job();
        $this->actingAs(User::factory()->create(['role' => 'student']));
        $this->postJson(route('applyJob'), ['job_id' => $job->id])->assertUnprocessable();
        $this->postJson(route('applyJob'), [
            'job_id' => $job->id,
            'resume' => UploadedFile::fake()->create('invalid.exe', 10, 'application/octet-stream'),
        ])->assertUnprocessable();
        $job->status = 0;
        $job->save();
        $this->submit($job)->assertJsonPath('status', false);
        Mail::assertNothingSent();
        $this->assertDatabaseCount('job_applications', 0);
    }

    public function test_guests_and_non_students_cannot_submit_or_trigger_emails(): void
    {
        Mail::fake();
        $job = $this->job();
        $this->submit($job)->assertUnauthorized();
        foreach (['admin', 'super_admin', 'employer'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->submit($job)->assertJsonPath('status', false);
        }
        $this->actingAs($job->user);
        $this->submit($job)->assertJsonPath('status', false)
            ->assertJsonPath('message', 'You cannot apply for your own job');
        Mail::assertNothingSent();
        $this->assertDatabaseCount('job_applications', 0);
    }

    public static function failedDeliveryOutcomes(): array
    {
        return [
            'poster fails' => [false, true],
            'student fails' => [true, false],
            'both fail' => [false, false],
        ];
    }

    #[DataProvider('failedDeliveryOutcomes')]
    public function test_mail_failures_preserve_application_and_attempt_both_emails(bool $posterSent, bool $studentSent): void
    {
        Exceptions::fake();
        $job = $this->job();
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);
        $posterPending = Mockery::mock(PendingMail::class);
        $studentPending = Mockery::mock(PendingMail::class);
        Mail::shouldReceive('to')->once()->with($job->user->email)->andReturn($posterPending);
        Mail::shouldReceive('to')->once()->with($student->email)->andReturn($studentPending);
        $posterException = new TransportException('Poster email failed');
        $studentException = new TransportException('Student email failed');
        $posterExpectation = $posterPending->shouldReceive('send')->once()->with(Mockery::type(JobNotificationEmail::class));
        $studentExpectation = $studentPending->shouldReceive('send')->once()->with(Mockery::type(ApplicationSubmissionConfirmation::class));
        if (! $posterSent) {
            $posterExpectation->andThrow($posterException);
        }
        if (! $studentSent) {
            $studentExpectation->andThrow($studentException);
        }
        $response = $this->submit($job)->assertOk()->assertJson([
            'status' => true, 'warning' => true,
            'notifications' => ['job_poster' => $posterSent, 'student' => $studentSent],
        ]);
        $this->assertStringContainsString('Your application is saved. Do not submit it again.', $response->json('message'));
        $this->assertDatabaseCount('job_applications', 1);
        $this->assertDatabaseCount('application_status_history', 1);
        if (! $posterSent) {
            Exceptions::assertReported(fn (TransportException $reported) => $reported === $posterException);
        }
        if (! $studentSent) {
            Exceptions::assertReported(fn (TransportException $reported) => $reported === $studentException);
        }
    }

    public function test_real_mailer_renders_both_emails_with_secure_document_links_and_no_attachments(): void
    {
        config(['mail.default' => 'array']);
        $job = $this->job();
        $student = User::factory()->create(['role' => 'student', 'name' => '<b>Student</b>']);
        $this->actingAs($student);
        $this->submit($job)->assertOk()->assertJsonPath('warning', false);

        $messages = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(2, $messages);
        $posterMail = $messages[0]->getOriginalMessage();
        $this->assertSame($job->user->email, $posterMail->getTo()[0]->getAddress());
        $this->assertSame($student->email, $posterMail->getReplyTo()[0]->getAddress());
        $this->assertSame('New application for Graduate Developer', $posterMail->getSubject());
        $this->assertStringContainsString('&lt;b&gt;Student&lt;/b&gt;', $posterMail->getHtmlBody());
        $this->assertStringContainsString('Example Company', $posterMail->getHtmlBody());
        $this->assertStringContainsString('Review applications and submitted documents', $posterMail->getHtmlBody());
        $this->assertCount(0, $posterMail->getAttachments());

        $studentMail = $messages[1]->getOriginalMessage();
        $this->assertSame($student->email, $studentMail->getTo()[0]->getAddress());
        $this->assertSame('officer@example.com', $studentMail->getReplyTo()[0]->getAddress());
        $this->assertStringContainsString('has been successfully submitted.', $studentMail->getHtmlBody());
        $this->assertStringContainsString('Example Company', $studentMail->getHtmlBody());
        $this->assertStringContainsString(route('account.myJobApplications'), $studentMail->getHtmlBody());
        $this->assertCount(0, $studentMail->getAttachments());
    }
}
