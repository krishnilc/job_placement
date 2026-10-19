<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'student@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'student',
        ]);

        $response = $this->post('/account/authenticate', [
            'email' => 'student@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/account/student-dashboard');
        $this->assertAuthenticatedAs(User::where('email', 'student@example.com')->first());
    }

    public static function loginRoles(): array
    {
        return [
            'student' => ['student', 'student.dashboard'],
            'employer' => ['employer', 'employer.dashboard'],
            'admin' => ['admin', 'admin.dashboard'],
            'super admin' => ['super_admin', 'admin.dashboard'],
            'management' => ['management', 'admin.dashboard'],
        ];
    }

    #[DataProvider('loginRoles')]
    public function test_successful_login_records_and_updates_last_login_for_every_role(string $role, string $dashboard): void
    {
        $this->travelTo(now()->startOfSecond());
        $user = User::factory()->create(['role' => $role, 'status' => 'active']);
        $this->assertNull($user->fresh()->last_login_at);
        $credentials = ['email' => $user->email, 'password' => 'password'];

        $this->post(route('account.authenticate'), $credentials)
            ->assertRedirect(route($dashboard));
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->fresh()->last_login_at->equalTo(now()));
        $this->assertSame('UTC', $user->fresh()->last_login_at->timezoneName);

        $firstLogin = $user->fresh()->last_login_at;
        $this->travel(1)->hour();
        $this->get(route('account.logout'))->assertRedirect(route('account.login'));
        $this->get(route('account.login'))->assertOk();
        $this->assertTrue($user->fresh()->last_login_at->equalTo($firstLogin));

        $this->post(route('account.authenticate'), $credentials)
            ->assertRedirect(route($dashboard));
        $this->assertTrue($user->fresh()->last_login_at->equalTo(now()));
    }

    public static function rejectedLogins(): array
    {
        return [
            'invalid password' => ['student', 'active', false, 'incorrect', 'account.login'],
            'invalid input' => ['student', 'active', false, '', 'account.login'],
            'pending student' => ['student', 'pending', false, 'password', 'account.login'],
            'blocked student' => ['student', 'blocked', false, 'password', 'account.login'],
            'pending employer' => ['employer', 'pending', false, 'password', 'account.login'],
            'blocked employer' => ['employer', 'blocked', false, 'password', 'account.login'],
            'pending management' => ['management', 'pending', false, 'password', 'account.login'],
            'blocked management' => ['management', 'blocked', false, 'password', 'account.login'],
            'pending admin' => ['admin', 'pending', false, 'password', 'account.login'],
            'blocked admin' => ['admin', 'blocked', false, 'password', 'account.login'],
            'pending super admin' => ['super_admin', 'pending', false, 'password', 'account.login'],
            'blocked super admin' => ['super_admin', 'blocked', false, 'password', 'account.login'],
            'unverified user' => ['student', 'active', true, 'password', 'verification.notice'],
        ];
    }

    #[DataProvider('rejectedLogins')]
    public function test_rejected_login_does_not_change_last_login(
        string $role,
        string $status,
        bool $verificationRequired,
        string $password,
        string $redirect
    ): void {
        $this->travelTo(now()->startOfSecond());
        $user = User::factory()->unverified()->create([
            'role' => $role,
            'status' => $status,
            'email_verification_required' => $verificationRequired,
        ]);

        foreach ([null, now()->subDay()] as $lastLogin) {
            $user->last_login_at = $lastLogin;
            $user->save();

            $this->post(route('account.authenticate'), [
                'email' => $user->email,
                'password' => $password,
            ])->assertRedirect(route($redirect));

            $this->assertGuest();
            $this->assertEquals($lastLogin, $user->fresh()->last_login_at);
        }
    }

    public function test_blocked_staff_account_cannot_continue_using_an_authenticated_session(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'blocked',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
