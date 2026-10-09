<?php

namespace Tests\Feature;

use App\Mail\EmployerJobPosted;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class EmployerJobPostedNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mail.contact.address' => 'officer@example.com']);
    }

    private function employer(): User
    {
        $organization = Organization::create([
            'name' => 'Example Company',
            'address' => 'Suva',
            'phone' => '1111111',
            'email' => 'example@example.com',
            'website_url' => 'https://example.com',
        ]);
        $employer = User::factory()->create([
            'role' => 'employer',
            'name' => 'Job Poster',
            'email' => 'poster@example.com',
        ]);
        $employer->employerProfile()->create(['organization_id' => $organization->id]);

        return $employer->fresh();
    }

    private function jobData(): array
    {
        return [
            'title' => 'Graduate Software Developer',
            'category' => Category::factory()->create()->id,
            'job_type' => JobType::factory()->create()->id,
            'vacancy' => 2,
            'closing_date' => '2026-12-31',
            'location' => 'Suva',
            'description' => "Build useful software.\nCollaborate with the team.",
            'responsibilities' => 'Develop and test applications.',
            'qualifications' => 'Relevant degree.',
            'keywords' => 'software, graduate',
            'experience' => 'Entry level',
        ];
    }

    public function test_employer_submission_notifies_placement_officer_after_saving_pending_job(): void
    {
        Mail::fake();
        $employer = $this->employer();
        $this->actingAs($employer)->postJson(route('account.saveJob'), $this->jobData())
            ->assertOk()->assertJson([
                'status' => true,
                'notification_sent' => true,
            ])
            ->assertJsonPath('message', 'Job submitted successfully and is awaiting admin approval. The Placement Officer has been notified.')
            ->assertSessionHas('success');

        $job = Job::where('title', 'Graduate Software Developer')->firstOrFail();
        $this->assertSame(0, $job->status);
        $this->assertSame($employer->id, $job->user_id);
        $this->assertSame($employer->employerProfile->organization_id, $job->organization_id);
        Mail::assertSent(EmployerJobPosted::class, fn ($mail) => $mail->hasTo('officer@example.com')
            && $mail->job->id === $job->id
            && $mail->job->user->id === $employer->id
            && $mail->envelope()->replyTo[0]->address === $employer->email);
        Mail::assertSentCount(1);
    }

    public function test_invalid_or_incomplete_job_is_not_saved_or_emailed(): void
    {
        Mail::fake();
        $employer = $this->employer();
        $this->actingAs($employer)->postJson(route('account.saveJob'), [])
            ->assertOk()->assertJson(['status' => false])->assertJsonStructure(['errors']);
        $employer->employerProfile->update(['organization_id' => null]);
        $this->postJson(route('account.saveJob'), $this->jobData())
            ->assertOk()->assertJsonPath('status', false)->assertJsonStructure(['errors' => ['company_name']]);
        $this->assertDatabaseCount('jobs', 0);
        Mail::assertNothingSent();
    }

    public function test_admin_created_job_does_not_send_employer_submission_notification(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $organization = Organization::create(['name' => 'Admin Company', 'phone' => '1111111', 'email' => 'admin-company@example.com']);
        $data = $this->jobData();
        unset($data['closing_date']);
        $data['organization_id'] = $organization->id;
        $data['job_status'] = 'active';

        $this->actingAs($admin)->postJson(route('admin.jobs.store'), $data)
            ->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseCount('jobs', 1);
        Mail::assertNothingSent();
    }

    public function test_notification_delivery_failure_preserves_job_and_warns_employer(): void
    {
        Exceptions::fake();
        $employer = $this->employer();
        $exception = new TransportException('Mail server unavailable');
        $pending = Mockery::mock(PendingMail::class);
        Mail::shouldReceive('to')->once()->with('officer@example.com')->andReturn($pending);
        $pending->shouldReceive('send')->once()->with(Mockery::type(EmployerJobPosted::class))
            ->andThrow($exception);

        $this->actingAs($employer)->postJson(route('account.saveJob'), $this->jobData())
            ->assertOk()->assertJson([
                'status' => true,
                'notification_sent' => false,
            ])
            ->assertJsonPath('message', 'Job submitted successfully and is awaiting admin approval. The job is saved and awaiting approval, but the Placement Officer notification email could not be sent. Please contact them directly.')
            ->assertSessionHas('error');

        $job = Job::where('title', 'Graduate Software Developer')->firstOrFail();
        $this->assertSame(0, $job->status);
        Exceptions::assertReported(fn (TransportException $reported) => $reported === $exception);
    }

    public function test_email_contains_posting_details_and_escapes_user_content(): void
    {
        config(['mail.default' => 'array']);
        $employer = $this->employer();
        $data = $this->jobData();
        $data['title'] = '<b>Graduate Developer</b>';
        $data['description'] = '<script>alert(1)</script>';
        $this->actingAs($employer)->postJson(route('account.saveJob'), $data)
            ->assertOk()->assertJsonPath('notification_sent', true);

        $messages = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $email = $messages[0]->getOriginalMessage();
        $this->assertSame('officer@example.com', $email->getTo()[0]->getAddress());
        $this->assertSame($employer->email, $email->getReplyTo()[0]->getAddress());
        $this->assertSame('Employer job awaiting approval: <b>Graduate Developer</b>', $email->getSubject());
        $this->assertStringContainsString('&lt;b&gt;Graduate Developer&lt;/b&gt;', $email->getHtmlBody());
        $this->assertStringContainsString('Example Company', $email->getHtmlBody());
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $email->getHtmlBody());
        $this->assertStringNotContainsString('<script>', $email->getHtmlBody());
        $this->assertStringContainsString('not yet publicly visible', $email->getHtmlBody());
        $this->assertStringContainsString('Review job postings', $email->getHtmlBody());
        $this->assertCount(0, $email->getAttachments());
    }
}
