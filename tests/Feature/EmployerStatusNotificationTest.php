<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationRequest;
use App\Models\User;
use App\Notifications\EmployerAccountStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class EmployerStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mail.contact.address' => 'officer@example.com']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    private function employer(): User
    {
        $user = User::factory()->create([
            'role' => 'employer', 'status' => 'pending', 'name' => 'Test Employer',
        ]);
        $organization = Organization::create(['name' => 'Test Company', 'address' => 'Suva', 'phone' => '1111111', 'email' => 'test@example.com']);
        $user->employerProfile()->create(['organization_id' => $organization->id]);

        return $user;
    }

    private function profileData(User $user, string $status): array
    {
        return [
            'name' => $user->name, 'email' => $user->email,
            'mobile' => $user->mobile, 'designation' => 'HR Manager',
            'organization_id' => $user->employerProfile->organization_id,
            'status' => $status,
        ];
    }

    public function test_admins_and_super_admins_notify_for_each_status_transition(): void
    {
        Notification::fake();
        $user = $this->employer();
        foreach (['admin', 'super_admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            foreach (['active', 'blocked', 'pending'] as $status) {
                $previous = $user->fresh()->status;
                $this->patchJson(route('admin.users.employers.status', $user), ['status' => $status])
                    ->assertOk()->assertJson(['status' => true, 'notification_sent' => true]);
                $this->assertSame($status, $user->fresh()->status);
                Notification::assertSentTo($user, EmployerAccountStatusChanged::class,
                    fn ($notification) => $notification->previousStatus === $previous
                        && $notification->currentStatus === $status);
            }
        }
        Notification::assertCount(6);
    }

    public function test_profile_editor_notifies_and_unchanged_status_does_not_resend(): void
    {
        Notification::fake();
        $user = $this->employer();
        $this->putJson(route('admin.users.update', $user), $this->profileData($user, 'active'))
            ->assertOk()->assertJsonPath('notification_sent', true)
            ->assertSessionHas('success', 'User information updated successfully! The employer has been notified by email.');
        $this->putJson(route('admin.users.update', $user), $this->profileData($user, 'active'))
            ->assertOk()->assertJsonPath('notification_sent', null);
        $this->patchJson(route('admin.users.employers.status', $user), ['status' => 'active'])
            ->assertOk()->assertJsonPath('notification_sent', null);
        Notification::assertSentToTimes($user, EmployerAccountStatusChanged::class, 1);
    }

    public function test_unlinked_organization_and_pending_request_prevent_activation_and_email(): void
    {
        Notification::fake();
        $user = $this->employer();
        $organizationId = $user->employerProfile->organization_id;
        $user->employerProfile->update(['organization_id' => null]);
        $this->patchJson(route('admin.users.employers.status', $user), ['status' => 'active'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->putJson(route('admin.users.update', $user), $this->profileData($user, 'active'))
            ->assertJson(['status' => false]);

        $user->employerProfile->update(['organization_id' => $organizationId]);
        OrganizationRequest::create([
            'user_id' => $user->id, 'name' => 'Requested Company',
            'address' => 'Suva', 'phone' => '1234567',
        ]);
        $this->patchJson(route('admin.users.employers.status', $user), ['status' => 'active'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->putJson(route('admin.users.update', $user), $this->profileData($user, 'active'))
            ->assertJson(['status' => false]);
        $this->assertSame('pending', $user->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_invalid_and_unauthorized_updates_send_no_email(): void
    {
        Notification::fake();
        $user = $this->employer();
        $this->patchJson(route('admin.users.employers.status', $user), ['status' => 'unknown'])
            ->assertUnprocessable();
        $student = User::factory()->create(['role' => 'student']);
        $this->patchJson(route('admin.users.employers.status', $student), ['status' => 'blocked'])
            ->assertNotFound();
        $this->actingAs(User::factory()->create(['role' => 'employer']));
        $this->patchJson(route('admin.users.employers.status', $user), ['status' => 'active'])
            ->assertRedirect();
        $this->putJson(route('admin.users.update', $user), $this->profileData($user, 'active'))
            ->assertForbidden();
        $this->assertSame('pending', $user->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_list_mail_failure_preserves_status_and_warns_admin(): void
    {
        Exceptions::fake();
        $user = $this->employer();
        $exception = new TransportException('Mail unavailable');
        Notification::shouldReceive('send')->once()->andThrow($exception);
        $this->patchJson(route('admin.users.employers.status', $user), ['status' => 'active'])
            ->assertOk()->assertJson(['status' => true, 'notification_sent' => false])
            ->assertJsonPath('message', 'The status was saved, but the notification email could not be sent. Please contact the employer directly.');
        $this->assertSame('active', $user->fresh()->status);
        Exceptions::assertReported(fn (TransportException $reported) => $reported === $exception);
        $this->get(route('admin.users.employers'))->assertOk()
            ->assertSee("response.notification_sent === false ? 'alert-warning' : 'alert-success'", false);
    }

    public function test_profile_mail_failure_preserves_status_and_shows_warning(): void
    {
        Exceptions::fake();
        $user = $this->employer();
        $exception = new TransportException('Mail unavailable');
        Notification::shouldReceive('send')->once()->andThrow($exception);
        $this->putJson(route('admin.users.update', $user), $this->profileData($user, 'blocked'))
            ->assertOk()->assertJsonPath('notification_sent', false)
            ->assertSessionHas('error')->assertSessionMissing('success');
        $this->assertSame('blocked', $user->fresh()->status);
        Exceptions::assertReported(fn (TransportException $reported) => $reported === $exception);
        $this->get(route('admin.users.employers'))->assertOk()
            ->assertSee('Please contact the employer directly.');
    }

    public function test_email_explains_employer_access_and_verification(): void
    {
        $user = $this->employer();
        $notification = new EmployerAccountStatusChanged('pending', 'active');
        $mail = $notification->toMail($user);
        $this->assertContains('You can now log in to manage job postings and student applications.', $mail->introLines);
        $this->assertSame(route('account.login'), $mail->actionUrl);
        $user->email_verification_required = true;
        $user->email_verified_at = null;
        $this->assertSame(route('verification.notice'), $notification->toMail($user)->actionUrl);
        $pending = (new EmployerAccountStatusChanged('active', 'pending'))->toMail($user);
        $this->assertContains('Your account is pending administrator approval. You cannot log in until your account has been approved.', $pending->introLines);
        $blocked = (new EmployerAccountStatusChanged('active', 'blocked'))->toMail($user);
        $this->assertContains('Your account has been blocked. You cannot log in. Please contact the Placement Officer for assistance.', $blocked->introLines);
    }

    public function test_email_is_rendered_and_delivered_to_employer_primary_address(): void
    {
        config(['mail.default' => 'array']);
        $user = $this->employer();
        $this->patchJson(route('admin.users.employers.status', $user), ['status' => 'active'])
            ->assertOk()->assertJsonPath('notification_sent', true);
        $messages = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $email = $messages[0]->getOriginalMessage();
        $this->assertSame($user->email, $email->getTo()[0]->getAddress());
        $this->assertSame('officer@example.com', $email->getReplyTo()[0]->getAddress());
        $this->assertSame('FNU Job Placement account status: Active', $email->getSubject());
        $this->assertStringContainsString('manage job postings and student applications', $email->getHtmlBody());
    }
}
