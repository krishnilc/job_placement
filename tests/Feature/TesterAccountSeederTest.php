<?php

namespace Tests\Feature;

use App\Models\College;
use App\Models\EmployerProfile;
use App\Models\Organization;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\TesterAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class TesterAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_separate_role_specific_accounts_with_working_logins(): void
    {
        $this->seed(TesterAccountSeeder::class);

        $this->assertDatabaseCount('users', 28);
        $this->assertDatabaseCount('student_profiles', 8);
        $this->assertDatabaseCount('employer_profiles', 8);
        $this->assertDatabaseCount('organizations', 8);

        foreach (['sd', 'wm', 'wcd', 'its'] as $prefix) {
            $accounts = [
                'student1' => 'student',
                'student2' => 'student',
                'employer1' => 'employer',
                'employer2' => 'employer',
                'admin' => 'admin',
                'management' => 'management',
                'super_admin' => 'super_admin',
            ];

            foreach ($accounts as $account => $role) {
                $user = User::where('email', "{$prefix}.{$account}@test.example")->firstOrFail();
                $this->assertSame($role, $user->role);
                $this->assertSame('active', $user->status);
                $this->assertTrue($user->hasVerifiedEmail());
                $this->assertFalse($user->needsEmailVerification());
                $this->assertTrue(Hash::check('test123', $user->password));
                $this->assertStringStartsWith($prefix.'_'.$account, $user->name);

                if ($role === 'student') {
                    $this->assertTrue($user->hasCompleteStudentProfile());
                    $this->assertLessThanOrEqual(9, strlen($user->student_id));
                    $this->assertNull($user->employerProfile);
                } elseif ($role === 'employer') {
                    $this->assertNotNull($user->employerProfile->organization);
                    $this->assertSame(
                        "{$prefix}_{$account} Test Organization",
                        $user->employerProfile->organization->name
                    );
                    $this->assertNotEmpty($user->employerProfile->organization->email);
                    $this->assertNotEmpty($user->employerProfile->organization->phone);
                    $this->assertNull($user->studentProfile);
                } else {
                    $this->assertNull($user->studentProfile);
                    $this->assertNull($user->employerProfile);
                }

                $dashboard = match ($role) {
                    'student' => 'student.dashboard',
                    'employer' => 'employer.dashboard',
                    default => 'admin.dashboard',
                };
                $this->post(route('account.authenticate'), [
                    'email' => $user->email,
                    'password' => 'test123',
                ])->assertRedirect(route($dashboard));
                $this->assertAuthenticatedAs($user);
                $this->get(route($dashboard))->assertOk();
                $this->get(route('account.logout'))->assertRedirect(route('account.login'));
                $this->assertGuest();
            }
        }

        $this->assertSame(8, StudentProfile::distinct()->count('student_id'));
        $this->assertSame(8, EmployerProfile::distinct()->count('organization_id'));
    }

    public function test_rerunning_restores_test_data_without_duplicates_or_changing_other_users(): void
    {
        $otherUser = User::factory()->create();
        $originalAttributes = $otherUser->fresh()->getAttributes();
        $this->seed(TesterAccountSeeder::class);
        $ids = User::orderBy('email')->pluck('id', 'email')->all();
        $student = User::where('email', 'sd.student1@test.example')->firstOrFail();
        $student->update(['password' => 'changed123', 'status' => 'blocked']);
        $student->studentProfile->update(['bio' => 'Changed bio']);

        $this->seed(TesterAccountSeeder::class);

        $this->assertSame($ids, User::orderBy('email')->pluck('id', 'email')->all());
        $this->assertDatabaseCount('users', 29);
        $this->assertDatabaseCount('student_profiles', 8);
        $this->assertDatabaseCount('employer_profiles', 8);
        $this->assertDatabaseCount('organizations', 8);
        $this->assertDatabaseCount('colleges', 1);
        $this->assertSame($originalAttributes, $otherUser->fresh()->getAttributes());
        $this->assertTrue(Hash::check('test123', $student->fresh()->password));
        $this->assertSame('active', $student->fresh()->status);
        $this->assertSame('Test student 1 for Systems Developer.', $student->fresh()->bio);
    }

    public function test_it_refuses_to_seed_outside_local_or_testing_environments(): void
    {
        $this->app->instance('env', 'production');

        try {
            $this->app->make(TesterAccountSeeder::class)->run();
            $this->fail('Expected tester account seeding to be refused in production.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Tester accounts may only be seeded in local or testing environments.',
                $exception->getMessage()
            );
        } finally {
            $this->app->instance('env', 'testing');
        }

        $this->assertDatabaseCount('users', 0);
        $this->assertSame(0, College::count());
        $this->assertSame(0, Organization::count());
    }
}
