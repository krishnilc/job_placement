<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_form_posts_to_a_working_endpoint_and_sends_a_valid_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->get(route('account.forgotPassword'))->assertOk()
            ->assertSee('action="'.route('password.email').'"', false);
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('account.forgotPassword'))->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $this->assertTrue(Password::tokenExists($user, $notification->token));
            $this->assertDatabaseMissing('password_reset_tokens', ['token' => $notification->token]);
            $mail = $notification->toMail($user);
            $this->get($mail->actionUrl)->assertOk()
                ->assertSee('Reset Password')
                ->assertSee($user->email)
                ->assertSee('value="'.$notification->token.'"', false);

            return true;
        });
        $this->assertGuest();
    }

    public function test_unknown_and_throttled_accounts_receive_the_same_confirmation(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $known = $this->post(route('password.email'), ['email' => $user->email]);
        $message = session('success');
        $known->assertRedirect(route('account.forgotPassword'));
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('success', $message);
        $this->post(route('password.email'), ['email' => 'missing@example.com'])
            ->assertSessionHas('success', $message);
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    }

    public function test_invalid_email_does_not_send_mail_and_requests_are_rate_limited(): void
    {
        Notification::fake();
        $this->post(route('password.email'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors('email');
        Notification::assertNothingSent();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('password.email'), ['email' => 'missing@example.com'])->assertRedirect();
        }
        $this->post(route('password.email'), ['email' => 'missing@example.com'])->assertStatus(429);
    }

    public function test_every_role_can_reset_without_changing_status_verification_or_last_login(): void
    {
        Event::fake([PasswordReset::class]);
        foreach (['student', 'employer', 'admin', 'super_admin', 'management'] as $role) {
            $user = User::factory()->unverified()->create([
                'role' => $role,
                'status' => 'blocked',
                'email_verification_required' => true,
                'remember_token' => 'old-remember-token',
                'last_login_at' => '2026-10-01 00:00:00',
            ]);
            $token = Password::createToken($user);
            $this->post(route('password.update'), [
                'token' => $token, 'email' => $user->email,
                'password' => 'new-secret123', 'password_confirmation' => 'new-secret123',
            ])->assertRedirect(route('account.login'))->assertSessionHas('success');
            $user->refresh();
            $this->assertTrue(Hash::check('new-secret123', $user->password));
            $this->assertNotSame('old-remember-token', $user->remember_token);
            $this->assertSame('blocked', $user->status);
            $this->assertNull($user->email_verified_at);
            $this->assertTrue($user->email_verification_required);
            $this->assertSame('2026-10-01 00:00:00', $user->last_login_at->format('Y-m-d H:i:s'));
            $this->assertFalse(Password::tokenExists($user, $token));
            $this->assertGuest();
        }
        Event::assertDispatchedTimes(PasswordReset::class, 5);
    }

    public function test_wrong_expired_and_reused_tokens_cannot_reset_a_password(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $token = Password::createToken($user);
        $payload = [
            'token' => $token, 'email' => $user->email,
            'password' => 'new-secret123', 'password_confirmation' => 'new-secret123',
        ];
        $this->post(route('password.update'), array_replace($payload, ['token' => 'wrong']))
            ->assertSessionHasErrors('email');
        $this->post(route('password.update'), array_replace($payload, ['email' => $other->email]))
            ->assertSessionHasErrors('email');
        $this->travel(61)->minutes();
        $this->post(route('password.update'), $payload)->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $payload['token'] = Password::createToken($user);
        $this->post(route('password.update'), $payload)->assertRedirect(route('account.login'));
        $this->post(route('password.update'), $payload)->assertSessionHasErrors('email');
    }

    public function test_password_validation_preserves_token_and_never_flashes_passwords(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        foreach ([
            ['short', 'short'],
            ['new-secret123', 'mismatch'],
        ] as [$password, $confirmation]) {
            $this->post(route('password.update'), [
                'token' => $token, 'email' => $user->email,
                'password' => $password, 'password_confirmation' => $confirmation,
            ])->assertSessionHasErrors('password')
                ->assertSessionMissing('_old_input.password')
                ->assertSessionMissing('_old_input.password_confirmation');
        }
        $this->assertTrue(Password::tokenExists($user, $token));
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_mail_failure_is_reported_and_shows_an_error(): void
    {
        Exceptions::fake();
        Notification::partialMock()->shouldReceive('send')->once()
            ->andThrow(new TransportException('Mail unavailable'));
        $user = User::factory()->create();
        $this->from(route('account.forgotPassword'))->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('account.forgotPassword'))->assertSessionHas('error');
        Exceptions::assertReported(TransportException::class);
    }

    public function test_reset_notification_uses_the_configured_mailer(): void
    {
        $user = User::factory()->create();
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('account.forgotPassword'))->assertSessionHas('success');
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_active_verified_user_can_login_with_the_replacement_password(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->post(route('password.update'), [
            'token' => Password::createToken($user), 'email' => $user->email,
            'password' => 'new-secret123', 'password_confirmation' => 'new-secret123',
        ])->assertRedirect(route('account.login'));
        $this->post(route('account.authenticate'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHas('error');
        $this->assertGuest();
        $this->post(route('account.authenticate'), ['email' => $user->email, 'password' => 'new-secret123'])
            ->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_reset_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post(route('password.update'), [])->assertSessionHasErrors('token');
        }
        $this->post(route('password.update'), [])->assertStatus(429);
    }
}
