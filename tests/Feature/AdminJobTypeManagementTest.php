<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminJobTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_search_job_types(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.jobTypes.create'))
            ->assertOk()
            ->assertSee('Add Job Type');

        $this->actingAs($admin)
            ->postJson(route('admin.jobTypes.store'), [
                'name' => 'Internship',
                'status' => '1',
            ])
            ->assertOk()
            ->assertJson(['status' => true]);

        $jobType = JobType::where('name', 'Internship')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.jobTypes.edit', $jobType->id))
            ->assertOk()
            ->assertSee('Edit Job Type');

        $this->actingAs($admin)
            ->putJson(route('admin.jobTypes.update', $jobType->id), [
                'name' => 'Graduate Internship',
                'status' => '0',
            ])
            ->assertOk()
            ->assertJson(['status' => true]);

        $this->actingAs($admin)
            ->get(route('admin.jobTypes', ['search' => 'Graduate']))
            ->assertOk()
            ->assertSee('Graduate Internship')
            ->assertDontSee('Full Time');
    }

    public function test_job_type_names_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        JobType::create(['name' => 'Contract', 'status' => 1]);

        $this->actingAs($admin)
            ->postJson(route('admin.jobTypes.store'), [
                'name' => 'Contract',
                'status' => '1',
            ])
            ->assertOk()
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_admin_job_creation_only_lists_active_job_types(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        JobType::create(['name' => 'Active Type', 'status' => 1]);
        JobType::create(['name' => 'Inactive Type', 'status' => 0]);

        $this->actingAs($admin)
            ->get(route('admin.jobs.create'))
            ->assertOk()
            ->assertSee('Active Type')
            ->assertDontSee('Inactive Type');
    }

    public function test_job_type_cannot_be_deleted_while_assigned_to_a_job(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $jobType = JobType::create(['name' => 'Placement', 'status' => 1]);
        $category = Category::factory()->create();
        Job::factory()->create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'job_type_id' => $jobType->id,
        ]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.jobTypes.destroy'), ['id' => $jobType->id])
            ->assertOk()
            ->assertJson(['status' => false]);

        $this->assertDatabaseHas('job_types', ['id' => $jobType->id]);
    }
}
