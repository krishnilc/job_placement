<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminJobOrganizationTest extends TestCase
{
    use RefreshDatabase;

    private function jobData(array $changes = []): array
    {
        return array_merge([
            'title' => 'Graduate Software Developer',
            'category' => Category::factory()->create()->id,
            'job_type' => JobType::create(['name' => 'Full Time', 'status' => 1])->id,
            'vacancy' => 2,
            'location' => 'Suva',
            'description' => 'Graduate developer placement.',
            'experience' => '0',
            'job_status' => 'active',
        ], $changes);
    }

    public function test_admin_and_super_admin_create_form_uses_organization_search(): void
    {
        foreach (['admin', 'super_admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('admin.jobs.create'))->assertOk()
                ->assertSee('Search your organization')
                ->assertSee('assets/js/organization-picker.js', false)
                ->assertSee('name="organization_id"', false)
                ->assertSee('Add an organization')
                ->assertDontSee('name="company_name"', false)
                ->assertDontSee('name="company_location"', false)
                ->assertDontSee('name="company_website"', false);
        }
    }

    public function test_both_admin_roles_create_jobs_with_selected_organization_details(): void
    {
        $organization = Organization::create([
            'name' => 'Selected Company',
            'address' => str_repeat('Address ', 100),
            'website_url' => 'https://example.com',
        ]);
        foreach (['admin', 'super_admin'] as $role) {
            $admin = User::factory()->create(['role' => $role]);
            $this->actingAs($admin)->postJson(route('admin.jobs.store'), $this->jobData([
                'organization_id' => $organization->id,
                'company_name' => 'Untrusted Company',
                'company_location' => 'Untrusted Address',
                'company_website' => 'https://untrusted.example.com',
            ]))->assertOk()->assertJson(['status' => true]);

            $job = Job::where('user_id', $admin->id)->firstOrFail();
            $this->assertSame($organization->id, $job->organization_id);
            $this->assertSame($organization->name, $job->company_name);
            $this->assertSame($organization->address, $job->company_location);
            $this->assertSame($organization->website_url, $job->company_website);
            $this->assertSame(1, $job->status);
        }
        $this->assertDatabaseCount('organizations', 1);
    }

    public function test_organization_selection_is_required_and_must_exist(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data = $this->jobData(['company_name' => 'Free Text Company']);
        foreach ([null, 99999, 'invalid'] as $organizationId) {
            $this->postJson(route('admin.jobs.store'), [...$data, 'organization_id' => $organizationId])
                ->assertOk()->assertJson(['status' => false])
                ->assertJsonStructure(['errors' => ['organization_id']]);
        }
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_job_edit_keeps_organization_details_read_only_for_both_admin_roles(): void
    {
        $organization = Organization::create([
            'name' => 'Shared Organization', 'address' => 'Shared Office',
            'website_url' => 'https://example.com',
        ]);
        $other = Organization::create(['name' => 'Another Organization']);
        $data = $this->jobData();
        foreach (['admin', 'super_admin'] as $role) {
            $admin = User::factory()->create(['role' => $role]);
            foreach ([$organization->id, null] as $organizationId) {
                $job = Job::factory()->create([
                    'user_id' => $admin->id, 'organization_id' => $organizationId,
                    'company_name' => 'Saved Organization', 'company_location' => 'Saved Office',
                    'company_website' => 'https://saved.example.com',
                    'category_id' => $data['category'], 'job_type_id' => $data['job_type'],
                ]);
                $original = $job->only(['organization_id', 'company_name', 'company_location', 'company_website']);
                $this->actingAs($admin)->get(route('admin.jobs.edit', $job->id))->assertOk()
                    ->assertSee('Organization details cannot be edited from this page.')
                    ->assertSee($organizationId ? 'Shared Organization' : 'Saved Organization')
                    ->assertDontSee('name="company_name"', false)
                    ->assertDontSee('name="organization_name"', false)
                    ->assertDontSee('name="organization_location"', false)
                    ->assertDontSee('name="organization_website"', false);
                $this->putJson(route('admin.jobs.update', $job->id), $data)
                    ->assertOk()->assertJson(['status' => true]);
                $this->assertSame($data['title'], $job->fresh()->title);
                $this->assertSame($original, $job->fresh()->only(array_keys($original)));
                foreach ([
                    'organization_id' => $other->id,
                    'company_name' => 'Changed Name', 'company_location' => 'Changed Office',
                    'company_website' => 'https://changed.example.com',
                    'organization_name' => 'Changed Name', 'organization_location' => 'Changed Office',
                    'organization_website' => 'https://changed.example.com',
                ] as $field => $value) {
                    $this->putJson(route('admin.jobs.update', $job->id), [...$data, $field => $value])
                        ->assertOk()->assertJson(['status' => false])
                        ->assertJsonStructure(['errors' => [$field]]);
                    $this->assertSame($original, $job->fresh()->only(array_keys($original)));
                }
            }
        }
        $this->assertSame('Shared Organization', $organization->fresh()->name);
    }

    public function test_organization_optional_details_and_job_settings_are_preserved(): void
    {
        $organization = Organization::create(['name' => 'Minimal Company']);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $data = $this->jobData(['organization_id' => $organization->id, 'isFeatured' => 1, 'salary' => '25000']);
        foreach (['pending' => 0, 'active' => 1, 'blocked' => 2] as $status => $expected) {
            $this->postJson(route('admin.jobs.store'), [...$data, 'job_status' => $status])
                ->assertOk()->assertJson(['status' => true]);
            $job = Job::latest('id')->firstOrFail();
            $this->assertNull($job->company_location);
            $this->assertNull($job->company_website);
            $this->assertSame($expected, $job->status);
            $this->assertSame(1, $job->isFeatured);
            $this->assertSame('25000', $job->salary);
        }
    }
}
