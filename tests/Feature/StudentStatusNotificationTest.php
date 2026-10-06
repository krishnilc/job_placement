<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\StudentAccountStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class StudentStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['mail.contact.address' => 'officer@example.com']);
    }

    private function student(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'student',
            'name' => 'Test Student',
            'status' => 'pending',
            'student_id' => '123456789',
        ], $attributes));
    }

    private function profileData(User $student, string $status): array
    {
        return [
            'name' => $student->name,
            'email' => $student->email,
            'student_id' => $student->student_id,
            'status' => $status,
        ];
    }

    public function test_admins_and_super_admins_notify_students_for_every_status_transition(): void
    {
        Notification::fake();
        $student = $this->student();
        foreach (['admin', 'super_admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            foreach (['active', 'blocked', 'pending'] as $status) {
                $previous = $student->fresh()->status;
                $this->patchJson(route('admin.users.students.status', $student), ['status' => $status])
                    ->assertOk()->assertJson(['status' => true, 'notification_sent' => true]);
                $this->assertSame($status, $student->fresh()->status);
                Notification::assertSentTo($student, StudentAccountStatusChanged::class,
                    fn ($notification) => $notification->previousStatus === $previous
                        && $notification->currentStatus === $status);
            }
        }
        Notification::assertCount(6);
    }

    public function test_profile_editor_notifies_alumni_when_status_changes(): void
    {
        Notification::fake();
        $student = $this->student(['designation' => 'Alumni']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->putJson(route('admin.users.update', $student), $this->profileData($student, 'active'))
            ->assertOk()->assertJson(['status' => true, 'notification_sent' => true])
            ->assertSessionHas('success', 'User information updated successfully! The student has been notified by email.');

        $this->assertSame('active', $student->fresh()->status);
        Notification::assertSentToTimes($student, StudentAccountStatusChanged::class, 1);
    }

    public function test_unchanged_status_sends_no_notification_from_either_editor(): void
    {
        Notification::fake();
        $student = $this->student();
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->patchJson(route('admin.users.students.status', $student), ['status' => 'pending'])
            ->assertOk()->assertJsonPath('notification_sent', null);
        $this->putJson(route('admin.users.update', $student), [
            ...$this->profileData($student, 'pending'), 'name' => 'Updated Student',
        ])->assertOk()->assertJsonPath('notification_sent', null);
        Notification::assertNothingSent();
    }

    public function test_invalid_status_and_non_student_target_send_no_notification(): void
    {
        Notification::fake();
        $student = $this->student();
        $employer = User::factory()->create(['role' => 'employer']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->patchJson(route('admin.users.students.status', $student), ['status' => 'unknown'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->putJson(route('admin.users.update', $student), $this->profileData($student, 'unknown'))
            ->assertOk()->assertJson(['status' => false]);
        $this->patchJson(route('admin.users.students.status', $employer), ['status' => 'blocked'])
            ->assertNotFound();
        $this->assertSame('pending', $student->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_employer_cannot_trigger_student_status_notifications(): void
    {
        Notification::fake();
        $student = $this->student();
        $this->actingAs(User::factory()->create(['role' => 'employer']));
        $this->patchJson(route('admin.users.students.status', $student), ['status' => 'active'])
            ->assertRedirect();
        $this->putJson(route('admin.users.update', $student), $this->profileData($student, 'active'))
            ->assertForbidden();
        $this->assertSame('pending', $student->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_list_delivery_failure_preserves_status_and_warns_admin(): void
    {
        Exceptions::fake();
        $student = $this->student();
        $exception = new TransportException('Mail unavailable');
        Notification::shouldReceive('send')->once()->andThrow($exception);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->patchJson(route('admin.users.students.status', $student), ['status' => 'active'])
            ->assertOk()->assertJson(['status' => true, 'notification_sent' => false])
            ->assertJsonPath('message', 'The status was saved, but the notification email could not be sent. Please contact the student directly.');
        $this->assertSame('active', $student->fresh()->status);
        Exceptions::assertReported(fn (TransportException $reported) => $reported === $exception);
        $this->get(route('admin.users.students'))->assertOk()
            ->assertSee("response.notification_sent === false ? 'alert-warning' : 'alert-success'", false);
    }

    public function test_profile_delivery_failure_preserves_status_and_displays_warning(): void
    {
        Exceptions::fake();
        $student = $this->student();
        $exception = new TransportException('Mail unavailable');
        Notification::shouldReceive('send')->once()->andThrow($exception);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->putJson(route('admin.users.update', $student), $this->profileData($student, 'blocked'))
            ->assertOk()->assertJson(['status' => true, 'notification_sent' => false])
            ->assertSessionHas('error')->assertSessionMissing('success');
        $this->assertSame('blocked', $student->fresh()->status);
        Exceptions::assertReported(fn (TransportException $reported) => $reported === $exception);
        $this->get(route('admin.users.students'))->assertOk()
            ->assertSee('User information and status were saved, but the notification email could not be sent.');
    }

    public function test_email_messages_explain_each_status_and_verification_requirement(): void
    {
        $student = $this->student();
        $active = (new StudentAccountStatusChanged('pending', 'active'))->toMail($student);
        $this->assertSame('FNU Job Placement account status: Active', $active->subject);
        $this->assertContains('Your account has been approved.', $active->introLines);
        $this->assertSame(route('account.login'), $active->actionUrl);
        $this->assertSame([['officer@example.com', 'Placement Officer']], $active->replyTo);

        $student->email_verification_required = true;
        $student->email_verified_at = null;
        $unverified = (new StudentAccountStatusChanged('pending', 'active'))->toMail($student);
        $this->assertSame(route('verification.notice'), $unverified->actionUrl);
        $this->assertContains('Please verify your email address before logging in. Use the link sent during registration, or request a new verification email below.', $unverified->introLines);

        $pending = (new StudentAccountStatusChanged('active', 'pending'))->toMail($student);
        $this->assertContains('Your account is pending administrator approval. You cannot log in until your account has been approved.', $pending->introLines);
        $this->assertSame(route('verification.notice'), $pending->actionUrl);

        $blocked = (new StudentAccountStatusChanged('active', 'blocked'))->toMail($student);
        $this->assertContains('Your account has been blocked. You cannot log in. Please contact the Placement Officer for assistance.', $blocked->introLines);
        $this->assertNull($blocked->actionUrl);
    }

    public function test_status_email_is_rendered_and_addressed_to_the_student(): void
    {
        config(['mail.default' => 'array']);
        $student = $this->student(['name' => '<b>Student</b>']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->patchJson(route('admin.users.students.status', $student), ['status' => 'active'])
            ->assertOk()->assertJsonPath('notification_sent', true);
        $messages = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $email = $messages[0]->getOriginalMessage();
        $this->assertSame($student->email, $email->getTo()[0]->getAddress());
        $this->assertSame('officer@example.com', $email->getReplyTo()[0]->getAddress());
        $this->assertSame('FNU Job Placement account status: Active', $email->getSubject());
        $this->assertStringContainsString('Your account has been approved.', $email->getHtmlBody());
        $this->assertStringContainsString('&lt;b&gt;Student&lt;/b&gt;', $email->getHtmlBody());
    }

    public function test_student_creation_does_not_send_a_status_change_notification(): void
    {
        Notification::fake();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->postJson(route('admin.users.students.store'), [
            'name' => 'New Student', 'email' => 'new@example.com',
            'mobile' => '1234567', 'student_id' => '987654321',
            'password' => 'secret123', 'confirm_password' => 'secret123',
        ])->assertOk()->assertJson(['status' => true]);
        Notification::assertNothingSent();
    }
}
