<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureAdminAccess;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWriteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_cannot_create_edit_update_or_delete_jobs_through_admin_routes(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $other = User::factory()->create(['role' => 'employer']);
        $organization = Organization::create(['name' => 'Test Organization']);
        $category = Category::factory()->create();
        $jobType = JobType::factory()->create();
        $data = [
            'title' => 'Unapproved publication',
            'category' => $category->id, 'job_type' => $jobType->id,
            'organization_id' => $organization->id, 'vacancy' => 1,
            'location' => 'Suva', 'description' => 'Unapproved job description',
            'experience' => '0', 'job_status' => 'active', 'isFeatured' => 1,
        ];
        $this->actingAs($employer);
        $this->get(route('admin.jobs.create'))->assertRedirect(route('admin.dashboard'));
        $this->postJson(route('admin.jobs.store'), $data)->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseCount('jobs', 0);

        foreach ([$employer, $other] as $owner) {
            $job = Job::factory()->create([
                'user_id' => $owner->id, 'category_id' => $category->id,
                'job_type_id' => $jobType->id, 'status' => 0, 'isFeatured' => 0,
            ]);
            $this->get(route('admin.jobs.edit', $job))->assertRedirect(route('admin.dashboard'));
            $this->putJson(route('admin.jobs.update', $job), $data)->assertRedirect(route('admin.dashboard'));
            $this->deleteJson(route('admin.jobs.destroy'), ['id' => $job->id])->assertRedirect(route('admin.dashboard'));
            $this->assertDatabaseHas('jobs', ['id' => $job->id, 'status' => 0, 'isFeatured' => 0, 'title' => $job->title]);
        }
    }

    public function test_only_super_admin_can_edit_or_delete_staff_accounts(): void
    {
        foreach (['admin', 'super_admin', 'management'] as $targetRole) {
            $staff = User::factory()->create(['role' => $targetRole]);
            $original = $staff->only(['name', 'email', 'role', 'mobile']);
            foreach (['employer', 'admin'] as $actorRole) {
                $this->actingAs(User::factory()->create(['role' => $actorRole]));
                $this->get(route('admin.users.edit', $staff))->assertForbidden();
                $this->putJson(route('admin.users.update', $staff), [
                    'name' => 'Changed Staff', 'email' => 'changed-'.$staff->id.'@example.com',
                    'mobile' => '1234567', 'role' => 'super_admin',
                ])->assertForbidden();
                $this->deleteJson(route('admin.users.destroy'), ['id' => $staff->id])->assertForbidden();
                $this->assertSame($original, $staff->fresh()->only(array_keys($original)));
            }
        }
    }

    public function test_admin_job_handlers_reject_employers_even_without_route_admin_middleware(): void
    {
        $this->withoutMiddleware(EnsureAdminAccess::class);
        $this->actingAs(User::factory()->create(['role' => 'employer']));
        $this->get(route('admin.jobs.create'))->assertForbidden();
        $this->get(route('admin.jobs.edit', 99999))->assertForbidden();
        $this->postJson(route('admin.jobs.store'), [])->assertForbidden();
        $this->putJson(route('admin.jobs.update', 99999), [])->assertForbidden();
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_employers_do_not_see_admin_job_write_controls(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $job = Job::factory()->create([
            'user_id' => $employer->id, 'category_id' => Category::factory(), 'job_type_id' => JobType::factory(),
        ]);
        $this->actingAs($employer)->get(route('admin.jobs'))->assertOk()
            ->assertDontSee(route('admin.jobs.create'), false)
            ->assertDontSee(route('admin.jobs.edit', $job), false);
    }

    public function test_super_admin_can_still_update_and_delete_staff(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $staff = User::factory()->create(['role' => 'admin']);
        $this->actingAs($superAdmin);
        $this->get(route('admin.users.edit', $staff))->assertOk();
        $this->putJson(route('admin.users.update', $staff), [
            'name' => 'Management Staff', 'email' => $staff->email,
            'mobile' => '1234567', 'role' => 'management',
        ])->assertOk()->assertJson(['status' => true]);
        $this->assertSame('management', $staff->fresh()->role);
        $this->deleteJson(route('admin.users.destroy'), ['id' => $staff->id])
            ->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }
}
