<?php

namespace Tests\Feature;

use App\Models\ApplicationStatus;
use App\Models\Category;
use App\Models\College;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\Organization;
use App\Models\OrganizationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class ManagementRoleTest extends TestCase
{
    use RefreshDatabase;

    private User $management;

    protected function setUp(): void
    {
        parent::setUp();
        $this->management = User::factory()->create(['role' => 'management', 'status' => 'active']);
    }

    private function application(): JobApplication
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $student = User::factory()->create(['role' => 'student']);
        $status = ApplicationStatus::create(['name' => 'Under Review', 'category' => 'In Progress', 'sort_order' => 1]);
        $job = Job::factory()->create([
            'user_id' => $employer->id,
            'job_type_id' => JobType::factory(),
            'category_id' => Category::factory(),
            'status' => 2,
        ]);

        return JobApplication::create([
            'job_id' => $job->id,
            'user_id' => $student->id,
            'employer_id' => $employer->id,
            'applied_at' => now(),
            'application_status_id' => $status->id,
        ]);
    }

    public function test_management_login_opens_admin_dashboard(): void
    {
        $this->post(route('account.authenticate'), [
            'email' => $this->management->email, 'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->management);
    }

    public function test_pending_and_blocked_management_cannot_log_in_or_use_existing_sessions(): void
    {
        foreach (['pending', 'blocked'] as $status) {
            $this->management->update(['status' => $status]);
            $this->post(route('account.authenticate'), [
                'email' => $this->management->email, 'password' => 'password',
            ])->assertRedirect(route('account.login'))->assertSessionHas('error');
            $this->assertGuest();
            $this->actingAs($this->management)->get(route('admin.dashboard'))->assertForbidden();
            $this->get(route('account.logout'))->assertRedirect(route('account.login'));
        }
    }

    public function test_management_can_view_all_admin_lists_without_mutation_controls(): void
    {
        $application = $this->application();
        College::create(['name' => 'Management Test College', 'code' => 'MTC', 'status' => 1]);
        $this->actingAs($this->management);

        foreach ([
            'admin.dashboard', 'admin.users.students', 'admin.users.employers',
            'admin.jobs', 'admin.jobApplications', 'admin.organizations.index',
            'admin.feedback', 'admin.colleges', 'admin.categories', 'admin.jobTypes',
        ] as $name) {
            $this->get(route($name))->assertOk()
                ->assertDontSee('Add Student')->assertDontSee('Add Employer')
                ->assertDontSee('Add Job')->assertDontSee('Add Organization')
                ->assertDontSee('Add Category')->assertDontSee('Add College/Center')
                ->assertDontSee('class="form-select form-select-sm status-select', false);
        }

        $this->get(route('admin.users.profile', $application->user_id))->assertOk()
            ->assertDontSee(route('admin.users.edit', $application->user_id), false);
        $this->get(route('admin.jobApplications'))->assertOk()
            ->assertViewHas('applications', fn ($applications) => $applications->modelKeys() === [$application->id])
            ->assertSee('Under Review')->assertSee('View History')
            ->assertDontSee('name="application_status_id"', false);
        $this->get(route('admin.jobs'))->assertOk()
            ->assertDontSee(route('admin.jobs.edit', $application->job_id), false);
    }

    public function test_management_can_view_pending_and_blocked_job_details_without_apply_or_save(): void
    {
        $application = $this->application();
        $this->actingAs($this->management);
        foreach ([0, 2] as $status) {
            $application->job->status = $status;
            $application->job->save();
            $this->get(route('jobDetail', $application->job_id))->assertOk()
                ->assertSee($application->job->title)
                ->assertDontSee('onclick="saveJob(', false)
                ->assertDontSee('onclick="openApplyModal(', false)
                ->assertSee(route('admin.jobs'), false);
        }
    }

    public function test_management_can_view_organizations_and_requests_without_decision_controls(): void
    {
        $organization = Organization::create(['name' => 'Example Organization', 'address' => 'Suva']);
        $request = OrganizationRequest::create([
            'user_id' => User::factory()->create(['role' => 'employer'])->id,
            'name' => 'Requested Organization', 'address' => 'Lautoka', 'status' => 'pending',
        ]);
        $this->actingAs($this->management);
        $this->get(route('admin.organizations.show', $organization))->assertOk()
            ->assertSee('Example Organization')->assertDontSee('Edit details');
        $this->get(route('admin.organizations.review', $request))->assertOk()
            ->assertSee('Requested Organization')
            ->assertDontSee('Link to this organization')
            ->assertDontSee('Approve new organization')->assertDontSee('Reject request');
    }

    public function test_every_admin_write_and_non_view_page_is_forbidden_even_with_direct_requests(): void
    {
        $application = $this->application();
        $organization = Organization::create(['name' => 'Unchanged Organization', 'address' => 'Suva']);
        $organizationRequest = OrganizationRequest::create([
            'user_id' => $application->employer_id, 'name' => 'Pending Organization',
            'address' => 'Suva', 'status' => 'pending',
        ]);
        $parameters = [
            'id' => $application->user_id, 'application' => $application->id,
            'organization' => $organization->id, 'organizationRequest' => $organizationRequest->id,
        ];
        $this->actingAs($this->management);
        $checked = 0;

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (! is_string($name) || ! str_starts_with($name, 'admin.')) {
                continue;
            }
            $method = $route->methods()[0];
            $isWrite = ! in_array($method, ['GET', 'HEAD'], true);
            $isNonViewPage = in_array($name, [
                'admin.users.admins', 'admin.users.admins.create', 'admin.users.edit',
                'admin.jobs.create', 'admin.jobs.edit',
                'admin.organizations.create', 'admin.organizations.edit',
                'admin.users.students.create', 'admin.users.employers.create',
                'admin.colleges.create', 'admin.colleges.edit',
                'admin.categories.create', 'admin.categories.edit',
                'admin.jobTypes.create', 'admin.jobTypes.edit',
            ], true);
            if (! $isWrite && ! $isNonViewPage) {
                continue;
            }
            $routeParameters = array_intersect_key($parameters, array_flip($route->parameterNames()));
            $this->json($method, route($name, $routeParameters), ['status' => 'active', 'name' => 'Changed'])
                ->assertForbidden();
            $checked++;
        }

        $this->assertGreaterThan(40, $checked);
        $this->assertDatabaseHas('organizations', ['id' => $organization->id, 'name' => 'Unchanged Organization']);
        $this->assertDatabaseHas('organization_requests', ['id' => $organizationRequest->id, 'status' => 'pending']);
        $this->assertDatabaseHas('jobs', ['id' => $application->job_id, 'status' => 2]);
        $this->assertDatabaseHas('job_applications', ['id' => $application->id, 'application_status_id' => $application->application_status_id]);
        $this->assertDatabaseCount('application_status_history', 0);
        $this->assertDatabaseCount('feedbacks', 0);
    }

    public function test_management_cannot_bypass_read_only_access_using_account_or_public_write_routes(): void
    {
        $application = $this->application();
        $this->actingAs($this->management);
        foreach ([
            ['POST', 'applyJob', []], ['POST', 'saveJob', []],
            ['POST', 'account.saveJob', []], ['POST', 'account.updateJob', ['id' => $application->job_id]],
            ['POST', 'account.deleteJob', []], ['POST', 'account.blockJob', []],
            ['POST', 'account.unblockJob', []], ['POST', 'account.removeJobApplication', []],
            ['POST', 'account.removeSavedJob', []], ['POST', 'account.feedback.store', ['application' => $application->id]],
        ] as [$method, $name, $parameters]) {
            $this->json($method, route($name, $parameters), ['job_id' => $application->job_id])->assertForbidden();
        }
        foreach (['account.createJob', 'account.editJob', 'employer.dashboard'] as $name) {
            $this->get(route($name, ['id' => $application->job_id]))->assertForbidden();
        }
        $this->assertDatabaseCount('jobs', 1);
        $this->assertDatabaseCount('job_applications', 1);
        $this->assertDatabaseCount('saved_jobs', 0);
    }

    public function test_management_can_export_reports_feedback_and_download_application_documents(): void
    {
        $application = $this->application();
        $this->actingAs($this->management);

        foreach (['excel', 'pdf'] as $format) {
            $this->get(route('admin.reports.export', ['report' => 'placement', 'format' => $format]))->assertOk()
                ->assertDownload($format === 'pdf' ? 'placement-report.pdf' : 'placement-report.csv');
            $this->get(route('admin.feedback.export', ['format' => $format]))->assertOk();
        }
        $filename = 'management-test-'.Str::uuid().'.pdf';
        $path = public_path('assets/applications/'.$filename);
        try {
            file_put_contents($path, 'Test resume');
            $application->update(['resume_file' => $filename]);
            $this->get(route('application.download', ['application' => $application->id, 'type' => 'resume']))
                ->assertOk()->assertHeader('Content-Type', 'application/pdf');
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_management_can_change_only_their_own_contact_details_and_password(): void
    {
        $other = User::factory()->create();
        $this->actingAs($this->management);
        foreach (['profile', 'editProfile', 'editPassword'] as $action) {
            $this->get(route('admin.account.'.$action))->assertOk();
            $this->get(route('account.'.$action))->assertRedirect(route('admin.account.'.$action));
        }
        $this->putJson(route('account.updateProfile'), [
            'id' => $other->id, 'name' => 'Management User',
            'email' => 'management@example.com', 'mobile' => '1234567', 'designation' => 'Director',
            'role' => 'super_admin', 'status' => 'blocked', 'organization_id' => 999,
        ])->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('users', [
            'id' => $this->management->id, 'name' => 'Management User',
            'designation' => 'Director', 'role' => 'management', 'status' => 'active',
        ]);
        $this->assertDatabaseHas('users', ['id' => $other->id, 'name' => $other->name]);
        $this->assertDatabaseMissing('student_profiles', ['user_id' => $this->management->id]);
        $this->assertDatabaseMissing('employer_profiles', ['user_id' => $this->management->id]);

        $this->postJson(route('account.updatePassword'), [
            'id' => $other->id, 'old_password' => 'password',
            'new_password' => 'new-secret123', 'confirm_password' => 'new-secret123',
        ])->assertOk()->assertJson(['status' => true]);
        $this->assertTrue(Hash::check('new-secret123', $this->management->fresh()->password));
        $this->assertTrue(Hash::check('password', $other->fresh()->password));
    }

    public function test_management_profile_picture_updates_only_the_authenticated_account(): void
    {
        $other = User::factory()->create();
        $this->actingAs($this->management);
        $imagePath = null;
        $thumbnailPath = null;
        try {
            $this->postJson(route('account.updateProfilePic'), [
                'id' => $other->id, 'profile_pic' => UploadedFile::fake()->image('management.png'),
            ])->assertOk()->assertJson(['status' => true]);
            $image = $this->management->fresh()->image;
            $imagePath = public_path('profile_pic/'.$image);
            $thumbnailPath = public_path('profile_pic/thumb/'.$image);
            $this->assertFileExists($imagePath);
            $this->assertFileExists($thumbnailPath);
            $this->assertNull($other->fresh()->image);
        } finally {
            foreach ([$imagePath, $thumbnailPath] as $path) {
                if ($path !== null && is_file($path)) {
                    unlink($path);
                }
            }
        }
    }

    public function test_super_admin_can_create_list_and_assign_management_accounts(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);
        $this->get(route('admin.users.admins.create'))->assertOk()->assertSee('Management (Read-only)');
        $this->postJson(route('admin.users.admins.store'), [
            'name' => 'Management Account', 'email' => 'director@example.com',
            'mobile' => '1234567', 'role' => 'management',
            'password' => 'secret123', 'confirm_password' => 'secret123',
        ])->assertOk()->assertJson(['status' => true]);
        $created = User::where('email', 'director@example.com')->firstOrFail();
        $this->assertSame('management', $created->role);
        $this->get(route('admin.users.admins'))->assertOk()->assertSee('Management (Read-only)')
            ->assertViewHas('users', fn ($users) => $users->contains('id', $created->id));
        $staff = User::factory()->create(['role' => 'admin', 'name' => 'Existing Staff']);
        $this->get(route('admin.users.edit', $staff->id))->assertOk()->assertSee('Management (Read-only)');
        $this->putJson(route('admin.users.update', $staff->id), [
            'name' => $staff->name, 'email' => $staff->email, 'mobile' => $staff->mobile,
            'role' => 'management', 'status' => 'active',
        ])->assertOk()->assertJson(['status' => true]);
        $this->assertSame('management', $staff->fresh()->role);
    }

    public function test_admin_and_employer_existing_write_access_is_preserved(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get(route('admin.jobs.create'))->assertOk();
        $this->post(route('admin.organizations.store'), [
            'name' => 'Admin Created Organization', 'address' => 'Suva',
        ])->assertRedirect();
        $this->assertDatabaseHas('organizations', ['name' => 'Admin Created Organization']);
        $this->get(route('admin.users.admins'))->assertRedirect();

        $application = $this->application();
        $this->actingAs($application->employer);
        $this->get(route('admin.jobApplications'))->assertOk()
            ->assertViewHas('applications', fn ($applications) => $applications->modelKeys() === [$application->id])
            ->assertSee('name="application_status_id"', false);
    }

    public function test_migration_rollback_cannot_promote_management_users(): void
    {
        $migration = require database_path('migrations/2026_10_07_000000_add_management_to_users_role_enum.php');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Reassign or remove management accounts');
        $migration->down();
    }
}
