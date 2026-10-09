<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function registration(string $role): array
    {
        $data = [
            'name' => 'Test Registrant',
            'email' => $role.'@example.com',
            'mobile' => '1234567',
            'password' => 'secret123',
            'confirm_password' => 'secret123',
            'role' => $role,
            'date_of_birth' => '2000-01-01',
        ];
        if ($role === 'student') {
            $data['student_id'] = '123456789';
        } elseif ($role === 'alumni') {
            $data['graduation_year'] = '2020';
        } else {
            $organization = Organization::create(['name' => 'Test Company', 'address' => 'Suva', 'phone' => '1111111', 'email' => 'test@example.com']);
            $data['designation'] = 'HR Manager';
            $data['organization_mode'] = 'existing';
            $data['organization_id'] = $organization->id;
        }

        return $data;
    }

    private function unverifiedUser(array $attributes = []): User
    {
        return User::factory()->unverified()->create(array_merge([
            'email_verification_required' => true,
            'status' => 'active',
            'password' => 'secret123',
        ], $attributes));
    }

    private function verificationUrl(User $user, int $minutes = 60): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes($minutes), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);
    }

    public function test_all_public_registration_roles_receive_verification_and_remain_pending(): void
    {
        Notification::fake();
        foreach (['student', 'alumni', 'employer'] as $role) {
            $this->post(route('account.processRegistration'), $this->registration($role))
                ->assertOk()
                ->assertJson(['status' => true, 'redirect' => route('verification.notice')]);
            $user = User::where('email', $role.'@example.com')->firstOrFail();
            $this->assertTrue($user->needsEmailVerification());
            $this->assertSame('pending', $user->status);
            Notification::assertSentTo($user, VerifyEmail::class);
        }
        $this->assertGuest();
        $this->get(route('verification.notice'))->assertOk()
            ->assertSee('Verify your email address')
            ->assertSee('Resend verification email');
    }

    public function test_invalid_registration_does_not_send_verification_mail(): void
    {
        Notification::fake();
        $this->post(route('account.processRegistration'), ['role' => 'student'])
            ->assertJson(['status' => false]);
        Notification::assertNothingSent();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_pending_guest_can_verify_without_activating_account(): void
    {
        Event::fake([Verified::class]);
        $user = $this->unverifiedUser(['status' => 'pending']);
        $url = $this->verificationUrl($user);

        $this->get($url)->assertRedirect(route('account.login'))
            ->assertSessionHas('success', 'Your email address has been verified. Your account is still pending administrator approval.');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertSame('pending', $user->fresh()->status);
        $this->assertGuest();
        Event::assertDispatched(Verified::class, fn (Verified $event) => $event->user->id === $user->id);

        $this->post(route('account.authenticate'), ['email' => $user->email, 'password' => 'secret123'])
            ->assertRedirect(route('account.login'))
            ->assertSessionHas('error', 'Your account is pending administrator approval.');
        $this->assertGuest();
    }

    public function test_verification_link_can_be_reopened_without_dispatching_duplicate_event(): void
    {
        Event::fake([Verified::class]);
        $user = $this->unverifiedUser();
        $url = $this->verificationUrl($user);
        $this->get($url)->assertRedirect(route('account.login'));
        $timestamp = $user->fresh()->email_verified_at;
        $this->get($url)->assertRedirect(route('account.login'));
        $this->assertEquals($timestamp, $user->fresh()->email_verified_at);
        Event::assertDispatchedTimes(Verified::class, 1);
    }

    public function test_expired_tampered_and_wrong_email_links_cannot_verify(): void
    {
        $user = $this->unverifiedUser();
        $this->get($this->verificationUrl($user, -1))->assertForbidden();
        $this->get($this->verificationUrl($user).'&tampered=1')->assertForbidden();
        $wrongHash = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id, 'hash' => sha1('different@example.com'),
        ]);
        $this->get($wrongHash)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_approved_unverified_account_cannot_login_then_can_after_verification(): void
    {
        $user = $this->unverifiedUser();
        $credentials = ['email' => $user->email, 'password' => 'secret123'];
        $this->post(route('account.authenticate'), $credentials)
            ->assertRedirect(route('verification.notice'))->assertSessionHas('error');
        $this->assertGuest();

        $this->get($this->verificationUrl($user))->assertRedirect(route('account.login'));
        $this->post(route('account.authenticate'), $credentials)->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_existing_unverified_accounts_retain_access_and_are_not_marked_verified(): void
    {
        foreach (['student', 'employer', 'admin'] as $role) {
            $user = User::factory()->unverified()->create([
                'role' => $role, 'status' => 'active', 'password' => 'secret123',
            ]);
            $this->assertFalse($user->fresh()->email_verification_required);
            $this->post(route('account.authenticate'), ['email' => $user->email, 'password' => 'secret123'])
                ->assertRedirect(route(match ($role) {
                    'employer' => 'employer.dashboard',
                    'admin' => 'admin.dashboard',
                    default => 'student.dashboard',
                }));
            $this->assertAuthenticatedAs($user);
            $this->assertFalse($user->fresh()->hasVerifiedEmail());
            $this->get(route('account.logout'));
        }
    }

    public function test_blocked_account_is_not_activated_by_verification(): void
    {
        $user = $this->unverifiedUser(['status' => 'blocked']);
        $this->get($this->verificationUrl($user))->assertRedirect(route('account.login'));
        $this->assertSame('blocked', $user->fresh()->status);
        $this->post(route('account.authenticate'), ['email' => $user->email, 'password' => 'secret123'])
            ->assertRedirect(route('account.login'))
            ->assertSessionHas('error', 'Your account has been blocked. Please contact the administrator.');
        $this->assertGuest();
    }

    public function test_resend_sends_only_to_accounts_needing_verification_and_uses_generic_response(): void
    {
        Notification::fake();
        $required = $this->unverifiedUser();
        $legacy = User::factory()->unverified()->create();
        $verified = User::factory()->create(['email_verification_required' => true]);

        foreach ([$required->email, $legacy->email, $verified->email, 'missing@example.com'] as $email) {
            $this->post(route('verification.send'), ['email' => $email])
                ->assertRedirect(route('verification.notice'))
                ->assertSessionHas('success', 'If this email belongs to an account that needs verification, a new verification link has been sent. Please check your inbox and spam folder.');
        }
        Notification::assertSentTo($required, VerifyEmail::class);
        Notification::assertNotSentTo([$legacy, $verified], VerifyEmail::class);
        Notification::assertCount(1);
    }

    public function test_resend_rejects_invalid_input_and_limits_requests(): void
    {
        Notification::fake();
        $this->from(route('verification.notice'))
            ->post(route('verification.send'), ['email' => 'invalid'])
            ->assertRedirect(route('verification.notice'))->assertSessionHasErrors('email');
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('verification.send'), ['email' => 'unknown@example.com'])->assertRedirect();
        }
        $this->post(route('verification.send'), ['email' => 'unknown@example.com'])->assertStatus(429);
        Notification::assertNothingSent();
    }

    public function test_initial_mail_failure_keeps_account_and_offers_resend(): void
    {
        Exceptions::fake();
        $exception = new TransportException('Mail unavailable');
        Notification::shouldReceive('send')->once()->andThrow($exception);
        $this->post(route('account.processRegistration'), $this->registration('student'))
            ->assertJson(['status' => true, 'redirect' => route('verification.notice')])
            ->assertSessionHas('error');
        $user = User::where('email', 'student@example.com')->firstOrFail();
        $this->assertTrue($user->needsEmailVerification());
        Exceptions::assertReported(fn (TransportException $reported) => $reported === $exception);
        $this->get(route('verification.notice'))->assertOk()
            ->assertSee('Do not register again.');
    }

    public function test_resend_mail_failure_is_reported_and_shows_error(): void
    {
        Exceptions::fake();
        $user = $this->unverifiedUser();
        $exception = new TransportException('Mail unavailable');
        Notification::shouldReceive('send')->once()->andThrow($exception);
        $this->post(route('verification.send'), ['email' => $user->email])
            ->assertRedirect(route('verification.notice'))->assertSessionHas('error');
        Exceptions::assertReported(fn (TransportException $reported) => $reported === $exception);
    }

    public function test_email_change_invalidates_verification_and_old_links_and_ends_session(): void
    {
        $user = User::factory()->create(['email_verification_required' => true]);
        $oldUrl = $this->verificationUrl($user);
        $user->email = 'new-address@example.com';
        $user->save();
        $this->assertTrue($user->fresh()->needsEmailVerification());
        $this->get($oldUrl)->assertForbidden();

        $this->actingAs($user)->get(route('account.profile'))
            ->assertRedirect(route('verification.notice'));
        $this->assertGuest();
        $this->get(route('verification.notice'))->assertOk()->assertSee('new-address@example.com');
    }

    public function test_unverified_sessions_cannot_access_json_actions(): void
    {
        $user = $this->unverifiedUser();
        $this->actingAs($user)->postJson(route('account.saveJob'))
            ->assertForbidden()
            ->assertJsonPath('redirect', route('verification.notice'));
        $this->assertGuest();
    }

    public function test_registration_mail_contains_a_working_signed_link(): void
    {
        config(['mail.default' => 'array']);
        $this->post(route('account.processRegistration'), $this->registration('student'))
            ->assertJson(['status' => true]);
        $messages = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $email = $messages[0]->getOriginalMessage();
        $this->assertSame('student@example.com', $email->getTo()[0]->getAddress());
        $this->assertSame('Verify Email Address', $email->getSubject());
        $this->assertSame(1, preg_match('/href="([^"]*\/account\/verify-email\/[^"]+)"/', $email->getHtmlBody(), $matches));
        $this->get(html_entity_decode($matches[1]))->assertRedirect(route('account.login'));
        $this->assertTrue(User::where('email', 'student@example.com')->firstOrFail()->hasVerifiedEmail());
    }
}
