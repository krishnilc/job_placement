<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\College;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Organization;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class StatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function organization(): Organization
    {
        return Organization::firstOrCreate(
            ['name' => 'Audit Company'],
            ['phone' => '1234567', 'email' => 'company@example.com'],
        );
    }

    private function account(string $role): User
    {
        $user = User::factory()->create([
            'name' => 'Audit Account', 'role' => $role, 'status' => 'pending',
            'student_id' => $role === 'student' ? '123456789' : null,
        ]);
        if ($role === 'employer') {
            $user->employerProfile()->create(['organization_id' => $this->organization()->id]);
        }

        return $user;
    }

    private function jobData(): array
    {
        return [
            'title' => 'Graduate Audit Developer',
            'category' => Category::factory()->create()->id,
            'job_type' => JobType::factory()->create()->id,
            'vacancy' => 1,
            'closing_date' => '2027-12-31',
            'location' => 'Suva',
            'description' => 'A graduate placement.',
            'experience' => '0',
        ];
    }

    private function job(User $owner, array $attributes = []): Job
    {
        return Job::factory()->create(array_merge([
            'user_id' => $owner->id,
            'category_id' => Category::factory()->create()->id,
            'job_type_id' => JobType::factory()->create()->id,
        ], $attributes));
    }

    public function test_self_registration_records_initial_status_for_students_alumni_and_employers(): void
    {
        Notification::fake();
        $organization = $this->organization();
        foreach (['student', 'alumni', 'employer'] as $role) {
            $this->postJson(route('account.processRegistration'), [
                'name' => 'New Audit Account', 'email' => $role.'@example.com',
                'mobile' => '1234567', 'password' => 'password', 'confirm_password' => 'password',
                'role' => $role, 'student_id' => $role === 'student' ? '123456789' : null,
                'date_of_birth' => '2000-01-01', 'graduation_year' => '2020',
                'designation' => 'Recruiter', 'organization_mode' => 'existing',
                'organization_id' => $organization->id,
            ])->assertOk()->assertJsonPath('status', true);

            $user = User::where('email', $role.'@example.com')->firstOrFail();
            $history = $user->statusHistories()->sole();
            $this->assertSame('created', $history->event);
            $this->assertNull($history->previous_status);
            $this->assertSame('pending', $history->current_status);
            $this->assertSame($user->role, $history->subject_kind);
            $this->assertNull($history->changed_by);
        }
    }

    public function test_staff_created_accounts_record_creator_and_initial_active_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Audit Admin']);
        $this->actingAs($admin);
        foreach (['student', 'employer'] as $role) {
            $this->postJson(route('admin.users.'.$role.'s.store'), [
                'name' => 'New Audit Account', 'email' => $role.'@example.com',
                'mobile' => '1234567', 'password' => 'password', 'confirm_password' => 'password',
                'student_id' => '123456789', 'designation' => 'Recruiter',
                'organization_id' => $this->organization()->id,
            ])->assertOk()->assertJsonPath('status', true);
            $user = User::where('email', $role.'@example.com')->firstOrFail();
            $history = $user->statusHistories()->sole();
            $this->assertSame('created', $history->event);
            $this->assertSame('active', $history->current_status);
            $this->assertSame($admin->id, $history->changed_by);
            $this->assertSame('Audit Admin', $history->changed_by_name);
        }
        $this->assertCount(0, $admin->statusHistories);
    }

    public function test_account_dropdowns_record_all_transitions_but_not_unchanged_or_rejected_updates(): void
    {
        Notification::fake();
        foreach (['student', 'employer'] as $role) {
            $user = $this->account($role);
            foreach (['admin', 'super_admin'] as $adminRole) {
                $admin = User::factory()->create(['role' => $adminRole]);
                $this->actingAs($admin);
                foreach (['active', 'blocked', 'pending'] as $status) {
                    $previous = $user->fresh()->status;
                    $this->postJson(route('admin.users.'.$role.'s.status', $user), [
                        '_method' => 'PATCH', 'status' => $status,
                    ])->assertOk()->assertJsonPath('status', true);
                    $history = $user->statusHistories()->firstOrFail();
                    $this->assertSame($previous, $history->previous_status);
                    $this->assertSame($status, $history->current_status);
                    $this->assertSame($admin->id, $history->changed_by);
                    $this->assertSame('changed', $history->event);
                }
            }
            $this->patchJson(route('admin.users.'.$role.'s.status', $user), ['status' => 'pending'])->assertOk();
            $this->patchJson(route('admin.users.'.$role.'s.status', $user), ['status' => 'invalid'])->assertUnprocessable();
            $this->actingAs(User::factory()->create(['role' => 'employer']))
                ->patchJson(route('admin.users.'.$role.'s.status', $user), ['status' => 'active'])->assertRedirect();
            $this->assertSame(7, $user->statusHistories()->count());
        }
    }

    public function test_profile_editors_also_record_account_status_changes(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $college = College::create(['code' => 'AUD', 'name' => 'Audit College', 'status' => 1]);
        foreach (['student', 'employer'] as $role) {
            $user = $this->account($role);
            $this->actingAs($admin)->putJson(route('admin.users.update', $user), [
                'name' => $user->name, 'email' => $user->email, 'mobile' => '1234567',
                'status' => 'active', 'student_id' => $user->student_id,
                'designation' => $role === 'student' ? 'Alumni' : 'Recruiter',
                'organization_id' => $user->employerProfile?->organization_id,
                'date_of_birth' => '2000-01-01', 'gender' => 'Male', 'marital_status' => 'Single',
                'residential_address' => 'Campus Road', 'city' => 'Suva', 'country' => 'Fiji',
                'high_school' => 'Audit School', 'high_school_graduation_year' => '2018',
                'college_id' => $college->id, 'degree' => 'Bachelor of Science', 'major' => 'Computing',
                'graduation_year' => '2022', 'skills' => 'PHP', 'bio' => 'Audit biography.',
                'availability' => 'Immediately',
            ])->assertOk()->assertJsonPath('status', true);
            $history = $user->statusHistories()->firstOrFail();
            $this->assertSame('pending', $history->previous_status);
            $this->assertSame('active', $history->current_status);
            $this->assertSame($admin->id, $history->changed_by);
        }
    }

    public function test_jobs_record_admin_creation_and_edits_and_employer_creation_block_and_unblock(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'super_admin']);
        $organization = $this->organization();
        $data = $this->jobData();
        $this->actingAs($admin)->postJson(route('admin.jobs.store'), [
            ...$data, 'organization_id' => $organization->id, 'job_status' => 'pending',
        ])->assertOk()->assertJsonPath('status', true);
        $job = Job::latest('id')->firstOrFail();
        $this->assertSame('0', $job->statusHistories()->sole()->current_status);
        $this->assertSame($admin->id, $job->statusHistories()->sole()->changed_by);
        foreach (['active' => '1', 'blocked' => '2', 'pending' => '0'] as $status => $expected) {
            $previous = (string) $job->fresh()->status;
            $this->putJson(route('admin.jobs.update', $job), [...$data, 'job_status' => $status])
                ->assertOk()->assertJsonPath('status', true);
            $history = $job->statusHistories()->firstOrFail();
            $this->assertSame($previous, $history->previous_status);
            $this->assertSame($expected, $history->current_status);
        }
        $this->putJson(route('admin.jobs.update', $job), [...$data, 'job_status' => 'pending'])->assertOk();
        $this->assertSame(4, $job->statusHistories()->count());

        $employer = $this->account('employer');
        $this->actingAs($employer)->postJson(route('account.saveJob'), $this->jobData())
            ->assertOk()->assertJsonPath('status', true);
        $job = Job::latest('id')->firstOrFail();
        $this->assertSame('created', $job->statusHistories()->sole()->event);
        $this->assertSame($employer->id, $job->statusHistories()->sole()->changed_by);
        foreach (['account.blockJob' => '2', 'account.unblockJob' => '1'] as $route => $expected) {
            $this->postJson(route($route), ['jobId' => $job->id])->assertOk()->assertJsonPath('status', true);
            $this->assertSame($expected, $job->statusHistories()->firstOrFail()->current_status);
            $this->assertSame($employer->id, $job->statusHistories()->firstOrFail()->changed_by);
        }
        $this->assertSame(3, $job->statusHistories()->count());
    }

    public function test_database_defaults_and_stale_model_updates_do_not_create_false_transitions(): void
    {
        $defaultUser = new User([
            'name' => 'Default Account', 'email' => 'default@example.com', 'password' => 'password',
        ]);
        $defaultUser->save();
        $this->assertSame('student', $defaultUser->statusHistories()->sole()->subject_kind);
        $this->assertSame($defaultUser->fresh()->status, $defaultUser->statusHistories()->sole()->current_status);
        $admin = User::factory()->create(['role' => 'admin']);
        $job = $this->job($admin);
        $this->assertSame('1', $job->statusHistories()->sole()->current_status);
        $job->status = '1';
        $job->save();
        $this->assertSame(1, $job->statusHistories()->count());

        $user = $this->account('student');
        $stale = $user->fresh();
        $user->status = 'active';
        $user->save();
        $stale->status = 'blocked';
        $stale->save();
        $this->assertSame('active', $user->statusHistories()->firstOrFail()->previous_status);
        $otherStale = $user->fresh();
        $user->status = 'pending';
        $user->save();
        $otherStale->status = 'pending';
        $otherStale->save();
        $this->assertSame(4, $user->statusHistories()->count());
        $user->name = 'Changed Name';
        $user->save();
        $this->assertSame(4, $user->statusHistories()->count());
    }

    public function test_audit_write_failure_rolls_back_status_updates_and_creation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = $this->account('student');
        $job = $this->job($admin, ['status' => 0]);
        StatusHistory::creating(function () {
            throw new RuntimeException('Audit write failed');
        });
        try {
            foreach ([$user, $job, User::factory()->make(['role' => 'student'])] as $subject) {
                $subject->status = $subject instanceof Job ? 1 : 'active';
                try {
                    $subject->save();
                    $this->fail('A failed audit write must fail the save.');
                } catch (RuntimeException $exception) {
                    $this->assertSame('Audit write failed', $exception->getMessage());
                }
            }
        } finally {
            StatusHistory::flushEventListeners();
        }
        $this->assertSame('pending', $user->fresh()->status);
        $this->assertSame(0, $job->fresh()->status);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('status_histories', 2);
    }

    public function test_notification_failure_does_not_remove_saved_history(): void
    {
        Exceptions::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = $this->account('student');
        Notification::shouldReceive('send')->once()->andThrow(new TransportException('Mail unavailable'));
        $this->actingAs($admin)->patchJson(route('admin.users.students.status', $user), ['status' => 'active'])
            ->assertOk()->assertJsonPath('notification_sent', false);
        $this->assertSame('active', $user->statusHistories()->firstOrFail()->current_status);
        $this->assertSame(2, $user->statusHistories()->count());
    }

    public function test_histories_are_restricted_paginated_isolated_and_escaped(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => '<b>Audit Admin</b>']);
        $student = $this->account('student');
        $employer = $this->account('employer');
        $job = $this->job($admin, ['title' => 'Audit Job']);
        $this->actingAs($admin);
        for ($i = 0; $i < 22; $i++) {
            $student->status = $i % 2 === 0 ? 'active' : 'blocked';
            $student->save();
        }
        foreach (['admin', 'super_admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $response = $this->get(route('admin.users.statusHistory', $student))
                ->assertOk()->assertSee('Previous status')->assertSee('New status')
                ->assertSee('&lt;b&gt;Audit Admin&lt;/b&gt;', false)
                ->assertDontSee('<b>Audit Admin</b>', false)
                ->assertDontSee('Audit Job');
            $this->assertCount(20, $response->viewData('histories'));
            $this->assertSame(23, $response->viewData('histories')->total());
            $this->get(route('admin.users.statusHistory', ['user' => $student, 'page' => 2]))
                ->assertOk()->assertSee('Created');
            $this->get(route('admin.users.statusHistory', $employer))->assertOk();
            $this->get(route('admin.jobs.statusHistory', $job))->assertOk()->assertSee('Active');
            $this->get(route('admin.users.statusHistory', $admin))->assertNotFound();
            foreach (['admin.users.students', 'admin.users.employers', 'admin.jobs'] as $list) {
                $this->get(route($list))->assertOk()->assertSee('View Status History');
            }
        }
        foreach (['management', 'student', 'employer'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            foreach ([route('admin.users.statusHistory', $student), route('admin.jobs.statusHistory', $job)] as $url) {
                $response = $this->get($url);
                if ($role === 'management') {
                    $response->assertForbidden();
                } else {
                    $response->assertRedirect();
                }
            }
        }
        $this->actingAs(User::factory()->create(['role' => 'management']));
        foreach (['admin.users.students', 'admin.users.employers', 'admin.jobs'] as $list) {
            $this->get(route($list))->assertOk()->assertDontSee('View Status History');
        }
        auth()->forgetGuards();
        $this->get(route('admin.users.statusHistory', $student))->assertRedirect();
        $this->get(route('admin.jobs.statusHistory', $job))->assertRedirect();
    }

    public function test_history_retains_actor_name_and_subject_details_after_deletion(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Original Admin']);
        $this->actingAs($admin);
        $student = $this->account('student');
        $history = $student->statusHistories()->sole();
        $admin->name = 'Renamed Admin';
        $admin->save();
        $this->assertSame('Original Admin', $history->fresh()->changed_by_name);
        $admin->delete();
        $this->assertNull($history->fresh()->changed_by);
        $student->delete();
        $this->assertSame('Audit Account', $history->fresh()->subject_name);
        $this->assertSame($student->id, $history->fresh()->subject_id);
    }

    public function test_migration_backfills_known_current_status_without_inventing_previous_changes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = $this->account('student');
        $employer = $this->account('employer');
        $job = $this->job($admin, ['status' => 2]);
        $migration = require database_path('migrations/2026_11_02_000001_create_status_histories_table.php');
        $migration->down();
        $migration->up();
        $this->assertDatabaseCount('status_histories', 3);
        foreach ([$student, $employer, $job] as $subject) {
            $history = $subject->statusHistories()->sole();
            $this->assertSame('baseline', $history->event);
            $this->assertNull($history->previous_status);
            $this->assertNull($history->changed_by);
            $this->assertSame((string) $subject->fresh()->status, $history->current_status);
        }
        $this->assertSame('Blocked', $job->statusHistories()->sole()->statusLabel('2'));
    }
}
